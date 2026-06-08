<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;

class FetchRssNews extends Command
{
    protected $signature = 'news:fetch-rss
        {--limit=6 : Số tin tối đa lấy từ mỗi nguồn}
        {--dry-run : Chỉ test, không lưu database}';

    protected $description = 'Fetch crypto news from RSS feeds, translate to Vietnamese, and save into news table';

    public function handle(): int
    {
        $sources = collect(config('news_feeds.sources', []))
            ->filter(fn ($source) => ($source['enabled'] ?? true) === true)
            ->values();

        if ($sources->isEmpty()) {
            $this->warn('Không có nguồn RSS nào được bật trong config/news_feeds.php.');
            return self::SUCCESS;
        }

        $limit = max((int) $this->option('limit'), 1);
        $isDryRun = (bool) $this->option('dry-run');

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($sources as $source) {
            $sourceName = $source['name'] ?? 'Unknown Source';
            $feedUrl = $source['feed_url'] ?? null;

            if (! $feedUrl) {
                $this->warn("Bỏ qua {$sourceName}: thiếu feed_url.");
                continue;
            }

            $this->info("Đang lấy tin từ {$sourceName}...");

            try {
                $httpClient = Http::timeout(25)
                    ->retry(2, 1000)
                    ->withHeaders([
                        'User-Agent' => 'CryptoBlog RSS Fetcher/1.0',
                        'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                    ]);

                if (! config('news_feeds.verify_ssl', true)) {
                    $httpClient = $httpClient->withoutVerifying();
                }

                $response = $httpClient->get($feedUrl);
            } catch (\Throwable $e) {
                $this->error("Không thể kết nối {$sourceName}: {$e->getMessage()}");
                continue;
            }

            if (! $response->successful()) {
                $this->error("Fetch {$sourceName} thất bại. HTTP status: {$response->status()}");
                continue;
            }

            $items = $this->parseFeedItems($response->body());

            if ($items->isEmpty()) {
                $this->warn("Không đọc được item nào từ {$sourceName}.");
                continue;
            }

            foreach ($items->take($limit) as $item) {
                $normalized = $this->normalizeItem($item, $source);

                if (! $normalized['title'] || ! $normalized['source_url']) {
                    $skippedCount++;
                    continue;
                }

                $exists = News::query()
                    ->where(function ($query) use ($normalized) {
                        $query->where('external_id', $normalized['external_id'])
                            ->orWhere('source_url', $normalized['source_url']);
                    })
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                if ($isDryRun) {
                    $this->line('[DRY RUN] ' . $normalized['title']);
                    $createdCount++;
                    continue;
                }

                News::create([
                    'title' => $normalized['title'],
                    'slug' => $this->uniqueSlug($normalized['title']),
                    'summary' => $normalized['summary'],
                    'content' => $normalized['content'],
                    'thumbnail' => $normalized['thumbnail'],
                    'source' => $normalized['source'],
                    'source_url' => $normalized['source_url'],
                    'source_feed' => $normalized['source_feed'],
                    'external_id' => $normalized['external_id'],
                    'is_auto' => true,
                    'category_id' => $normalized['category_id'],
                    'published_at' => $normalized['published_at'],
                    'fetched_at' => now(),
                ]);

                $createdCount++;

                $this->line('Đã lưu: ' . $normalized['title']);
            }
        }

        $this->info("Hoàn tất. Tạo mới: {$createdCount}. Bỏ qua/trùng: {$skippedCount}.");

        return self::SUCCESS;
    }

    private function parseFeedItems(string $xmlContent)
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlContent, SimpleXMLElement::class, LIBXML_NOCDATA);

        if (! $xml) {
            libxml_clear_errors();
            return collect();
        }

        if (isset($xml->channel->item)) {
            return collect($xml->channel->item);
        }

        if (isset($xml->entry)) {
            return collect($xml->entry);
        }

        return collect();
    }

    private function normalizeItem(SimpleXMLElement $item, array $source): array
    {
        $sourceName = $source['name'] ?? 'Unknown Source';
        $feedUrl = $source['feed_url'] ?? null;

        $originalTitle = $this->cleanText((string) ($item->title ?? ''));
        $sourceUrl = $this->extractLink($item);
        $originalDescription = $this->extractDescription($item);
        $thumbnail = $this->extractThumbnail($item);
        $publishedAt = $this->extractPublishedAt($item);
        $rssCategories = $this->extractRssCategories($item);

        $translatedTitle = $this->translateToVietnamese($originalTitle);
        $translatedDescription = $this->translateToVietnamese($originalDescription);

        $title = $translatedTitle ?: $originalTitle;

        $summary = $this->buildVietnameseSummary(
            title: $title,
            translatedDescription: $translatedDescription,
            originalDescription: $originalDescription,
            sourceName: $sourceName
        );

        $categoryId = $this->resolveCategoryId(
            originalTitle: $originalTitle,
            translatedTitle: $title,
            originalSummary: $originalDescription,
            translatedSummary: $summary,
            rssCategories: $rssCategories,
            defaultCategory: $source['default_category'] ?? null
        );

        $content = $this->buildVietnameseContent(
            title: $title,
            summary: $summary,
            translatedDescription: $translatedDescription,
            originalDescription: $originalDescription,
            sourceName: $sourceName,
            sourceUrl: $sourceUrl,
            publishedAt: $publishedAt
        );

        $externalId = $this->externalId($sourceName, $sourceUrl, $originalTitle);

        return [
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'thumbnail' => $thumbnail,
            'source' => $sourceName,
            'source_url' => $sourceUrl,
            'source_feed' => $feedUrl,
            'external_id' => $externalId,
            'category_id' => $categoryId,
            'published_at' => $publishedAt,
        ];
    }

    private function buildVietnameseSummary(
        string $title,
        string $translatedDescription,
        string $originalDescription,
        string $sourceName
    ): string {
        $mainText = $translatedDescription ?: $originalDescription;

        if (! $mainText) {
            return "Bản tin từ {$sourceName} cập nhật một diễn biến mới liên quan đến thị trường tiền mã hóa. Người đọc nên xem thêm nguồn gốc để có đầy đủ bối cảnh.";
        }

        $mainText = trim($mainText);

        if (mb_strlen($mainText) < 180) {
            return $mainText . " Đây là thông tin được hệ thống tổng hợp tự động từ {$sourceName}, giúp người đọc nắm nhanh diễn biến chính trước khi xem bài viết gốc.";
        }

        return Str::limit($mainText, 650);
    }

    private function buildVietnameseContent(
        string $title,
        string $summary,
        string $translatedDescription,
        string $originalDescription,
        string $sourceName,
        ?string $sourceUrl,
        Carbon $publishedAt
    ): string {
        $mainText = trim($translatedDescription ?: $originalDescription ?: $summary);

        $paragraphs = [];

        $paragraphs[] = $summary;

        $paragraphs[] = "Theo nguồn tin từ {$sourceName}, nội dung này phản ánh một diễn biến mới trong thị trường crypto và có thể liên quan đến giá tài sản số, hoạt động của các doanh nghiệp blockchain, xu hướng đầu tư hoặc thay đổi trong hệ sinh thái tiền mã hóa.";

        if ($mainText && mb_strlen($mainText) > mb_strlen($summary) + 40) {
            $paragraphs[] = Str::limit($mainText, 900);
        }

        $paragraphs[] = "Điểm đáng chú ý là thông tin này có thể giúp người đọc theo dõi bối cảnh thị trường tốt hơn, đặc biệt khi các biến động về Bitcoin, Ethereum, stablecoin, DeFi, sàn giao dịch hoặc chính sách quản lý thường ảnh hưởng trực tiếp đến tâm lý nhà đầu tư.";

        $paragraphs[] = "Tuy nhiên, đây là tin được CryptoBlog tự động tổng hợp từ RSS nên nội dung chỉ nên được xem như bản tóm tắt tham khảo. Người dùng nên đọc thêm bài gốc để kiểm chứng chi tiết, ngữ cảnh đầy đủ và các cập nhật mới nhất từ nguồn xuất bản.";

        $paragraphs[] = "Thời gian ghi nhận tin: " . $publishedAt->format('d/m/Y H:i') . ".";

        if ($sourceUrl) {
            $paragraphs[] = "Nguồn gốc bài viết: {$sourceUrl}";
        }

        return collect($paragraphs)
            ->map(fn ($paragraph) => trim(preg_replace('/\s+/', ' ', $paragraph)))
            ->filter()
            ->implode("\n\n");
    }

    private function translateToVietnamese(?string $text): string
    {
        $text = trim((string) $text);

        if (! $text) {
            return '';
        }

        if (! config('news_feeds.translate_to_vietnamese', true)) {
            return $text;
        }

        $driver = config('news_feeds.translation_driver', 'google_free');

        if ($driver === 'none') {
            return $text;
        }

        if ($this->looksVietnamese($text)) {
            return $text;
        }

        if ($driver === 'google_free') {
            return $this->translateViaGoogleFree($text);
        }

        return $text;
    }

    private function translateViaGoogleFree(string $text): string
    {
        try {
            $chunks = $this->splitText($text, 900);

            $translatedChunks = [];

            foreach ($chunks as $chunk) {
                $httpClient = Http::timeout(20)
                    ->retry(1, 800)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 CryptoBlog Local Demo',
                    ]);

                if (! config('news_feeds.verify_ssl', true)) {
                    $httpClient = $httpClient->withoutVerifying();
                }

                $response = $httpClient->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'auto',
                    'tl' => 'vi',
                    'dt' => 't',
                    'q' => $chunk,
                ]);

                if (! $response->successful()) {
                    $translatedChunks[] = $chunk;
                    continue;
                }

                $json = $response->json();

                $translatedText = '';

                if (is_array($json) && isset($json[0]) && is_array($json[0])) {
                    foreach ($json[0] as $segment) {
                        if (isset($segment[0])) {
                            $translatedText .= $segment[0];
                        }
                    }
                }

                $translatedChunks[] = trim($translatedText) ?: $chunk;
            }

            return trim(implode(' ', $translatedChunks));
        } catch (\Throwable $e) {
            return $text;
        }
    }

    private function splitText(string $text, int $limit = 900): array
    {
        $text = trim($text);

        if (mb_strlen($text) <= $limit) {
            return [$text];
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $text) ?: [$text];

        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            if (mb_strlen($current . ' ' . $sentence) > $limit) {
                if ($current) {
                    $chunks[] = trim($current);
                }

                $current = $sentence;
            } else {
                $current .= ' ' . $sentence;
            }
        }

        if (trim($current)) {
            $chunks[] = trim($current);
        }

        return $chunks ?: [$text];
    }

    private function looksVietnamese(string $text): bool
    {
        return (bool) preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/iu', $text);
    }

    private function extractLink(SimpleXMLElement $item): ?string
    {
        $link = trim((string) ($item->link ?? ''));

        if ($link) {
            return $link;
        }

        if (isset($item->link)) {
            foreach ($item->link as $atomLink) {
                $attributes = $atomLink->attributes();

                if (isset($attributes['href'])) {
                    return trim((string) $attributes['href']);
                }
            }
        }

        return null;
    }

    private function extractDescription(SimpleXMLElement $item): string
    {
        $description = (string) ($item->description ?? $item->summary ?? '');

        $namespaces = $item->getNamespaces(true);

        if (! $description && isset($namespaces['content'])) {
            $contentChildren = $item->children($namespaces['content']);

            if (isset($contentChildren->encoded)) {
                $description = (string) $contentChildren->encoded;
            }
        }

        return $this->cleanText($description);
    }

    private function extractThumbnail(SimpleXMLElement $item): ?string
    {
        if (isset($item->enclosure)) {
            foreach ($item->enclosure as $enclosure) {
                $attributes = $enclosure->attributes();

                if (isset($attributes['url'])) {
                    return trim((string) $attributes['url']);
                }
            }
        }

        $namespaces = $item->getNamespaces(true);

        if (isset($namespaces['media'])) {
            $mediaChildren = $item->children($namespaces['media']);

            if (isset($mediaChildren->content)) {
                foreach ($mediaChildren->content as $mediaContent) {
                    $attributes = $mediaContent->attributes();

                    if (isset($attributes['url'])) {
                        return trim((string) $attributes['url']);
                    }
                }
            }

            if (isset($mediaChildren->thumbnail)) {
                foreach ($mediaChildren->thumbnail as $mediaThumbnail) {
                    $attributes = $mediaThumbnail->attributes();

                    if (isset($attributes['url'])) {
                        return trim((string) $attributes['url']);
                    }
                }
            }
        }

        $rawHtml = (string) ($item->description ?? '');

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $rawHtml, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractPublishedAt(SimpleXMLElement $item): Carbon
    {
        $date = (string) (
            $item->pubDate
            ?? $item->published
            ?? $item->updated
            ?? ''
        );

        if (! $date) {
            return now();
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return now();
        }
    }

    private function extractRssCategories(SimpleXMLElement $item): array
    {
        $categories = [];

        if (isset($item->category)) {
            foreach ($item->category as $category) {
                $value = $this->cleanText((string) $category);

                if ($value) {
                    $categories[] = $value;
                }
            }
        }

        return $categories;
    }

    private function resolveCategoryId(
        string $originalTitle,
        string $translatedTitle,
        string $originalSummary,
        string $translatedSummary,
        array $rssCategories = [],
        ?string $defaultCategory = null
    ): ?int {
        $categories = Category::query()
            ->when(Schema::hasColumn('categories', 'is_active'), function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            return null;
        }

        $text = Str::lower(
            $originalTitle . ' ' .
            $translatedTitle . ' ' .
            $originalSummary . ' ' .
            $translatedSummary . ' ' .
            implode(' ', $rssCategories)
        );

        $keywordMap = config('news_feeds.category_keywords', []);

        foreach ($keywordMap as $categoryName => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($text, Str::lower($keyword))) {
                    $matchedCategory = $this->findCategoryByName($categories, $categoryName);

                    if ($matchedCategory) {
                        return $matchedCategory->id;
                    }
                }
            }
        }

        if ($defaultCategory) {
            $defaultMatchedCategory = $this->findCategoryByName($categories, $defaultCategory);

            if ($defaultMatchedCategory) {
                return $defaultMatchedCategory->id;
            }
        }

        $fallback = $categories->first(function ($category) {
            $name = Str::lower($category->name);

            return Str::contains($name, ['crypto', 'tin tức', 'thi truong', 'thị trường', 'market']);
        });

        return ($fallback ?? $categories->first())?->id;
    }

    private function findCategoryByName($categories, string $targetName)
    {
        $target = Str::lower($targetName);
        $targetSlug = Str::slug($targetName);

        return $categories->first(function ($category) use ($target, $targetSlug) {
            $name = Str::lower($category->name);
            $slug = $category->slug ?? Str::slug($category->name);

            return $name === $target
                || $slug === $targetSlug
                || Str::contains($name, $target)
                || Str::contains($target, $name);
        });
    }

    private function cleanText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function externalId(string $sourceName, ?string $link, string $title): string
    {
        return sha1($sourceName . '|' . ($link ?: $title));
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if (! $baseSlug) {
            $baseSlug = 'auto-news-' . Str::random(8);
        }

        $slug = $baseSlug;
        $counter = 2;

        while (News::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}