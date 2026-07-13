<?php

namespace App\Services;

use App\Models\News;
use App\Models\NewsRelatedLink;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsRelatedLinkService
{
    public function __construct(
        private readonly NewsContentPresenter $contentPresenter
    ) {
    }

    /**
     * Phân tích bài hiện tại, tìm các bài cũ phù hợp nhất và lưu quan hệ.
     */
    public function generateFor(News $news, ?int $limit = null): Collection
    {
        $news->loadMissing('category');

        $limit = max(
            1,
            min(
                $limit ?? (int) config('news_related.limit', 4),
                8
            )
        );

        $candidatePool = max(
            20,
            (int) config('news_related.candidate_pool', 250)
        );

        /*
         * Chỉ lấy những bài đã tồn tại trước bài hiện tại để tránh vòng liên kết
         * và đúng với nghiệp vụ “tin hiện tại tham chiếu các tin trước đó”.
         */
        $referenceDate = $news->published_at ?: $news->created_at;

        $candidates = News::query()
            ->with('category')
            ->where('id', '<', $news->id)
            ->whereNotNull('slug')
            ->when($referenceDate, function ($query) use ($referenceDate) {
                $query->whereRaw(
                    'COALESCE(published_at, created_at) <= ?',
                    [$referenceDate->toDateTimeString()]
                );
            })
            ->latest('id')
            ->limit($candidatePool)
            ->get();

        $currentProfile = $this->buildProfile($news);
        $minimumScore = (float) config('news_related.minimum_score', 24);

        $ranked = $candidates
            ->map(function (News $candidate) use ($news, $currentProfile) {
                return $this->scoreCandidate(
                    $news,
                    $currentProfile,
                    $candidate
                );
            })
            ->filter(fn (?array $result) => $result !== null)
            ->filter(fn (array $result) => $result['score'] >= $minimumScore)
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        $blockCount = max(
            1,
            count($this->contentPresenter->toBlocks($news->content))
        );

        $positions = $this->calculatePositions(
            $blockCount,
            $ranked->count()
        );

        DB::transaction(function () use ($news, $ranked, $positions): void {
            NewsRelatedLink::query()
                ->where('news_id', $news->id)
                ->delete();

            foreach ($ranked as $index => $result) {
                NewsRelatedLink::create([
                    'news_id' => $news->id,
                    'related_news_id' => $result['news']->id,
                    'score' => round($result['score'], 2),
                    'display_order' => $index + 1,
                    'paragraph_index' => $positions[$index] ?? 1,
                    'matched_keywords' => $result['matched_keywords'],
                    'reason' => $result['reason'],
                ]);
            }

            $news->forceFill([
                'related_links_generated_at' => now(),
            ])->saveQuietly();
        });

        return NewsRelatedLink::query()
            ->with(['relatedNews.category'])
            ->where('news_id', $news->id)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * @return array{
     *     title_text: string,
     *     all_text: string,
     *     title_tokens: array<int, string>,
     *     all_tokens: array<int, string>,
     *     entities: array<int, string>
     * }
     */
    private function buildProfile(News $news): array
    {
        $titleText = $this->normalizeText($news->title);

        $plainContent = html_entity_decode(
            strip_tags((string) $news->content),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $allText = $this->normalizeText(
            implode(' ', [
                $news->title,
                $news->summary,
                $plainContent,
                $news->category?->name,
            ])
        );

        return [
            'title_text' => $titleText,
            'all_text' => $allText,
            'title_tokens' => $this->tokenize($titleText),
            'all_tokens' => $this->tokenize($allText),
            'entities' => $this->extractEntities($allText),
        ];
    }

    /**
     * @param array<string, mixed> $currentProfile
     * @return array<string, mixed>|null
     */
    private function scoreCandidate(
        News $current,
        array $currentProfile,
        News $candidate
    ): ?array {
        $candidateProfile = $this->buildProfile($candidate);
        $scoreConfig = config('news_related.scores', []);

        $matchedEntities = array_values(array_intersect(
            $currentProfile['entities'],
            $candidateProfile['entities']
        ));

        $matchedTitleTokens = array_values(array_intersect(
            $currentProfile['title_tokens'],
            $candidateProfile['title_tokens']
        ));

        $titleToBodyTokens = array_values(array_diff(
            array_intersect(
                $currentProfile['title_tokens'],
                $candidateProfile['all_tokens']
            ),
            $matchedTitleTokens
        ));

        $matchedBodyTokens = array_values(array_diff(
            array_intersect(
                $currentProfile['all_tokens'],
                $candidateProfile['all_tokens']
            ),
            $matchedTitleTokens,
            $titleToBodyTokens
        ));

        $sameCategory = $current->category_id
            && $candidate->category_id
            && (int) $current->category_id === (int) $candidate->category_id;

        $score = 0.0;

        if ($sameCategory) {
            $score += (float) ($scoreConfig['same_category'] ?? 18);
        }

        $score += min(
            count($matchedEntities)
                * (float) ($scoreConfig['entity_match'] ?? 12),
            48
        );

        $score += min(
            count($matchedTitleTokens)
                * (float) ($scoreConfig['title_token_match'] ?? 6),
            30
        );

        $score += min(
            count($titleToBodyTokens)
                * (float) ($scoreConfig['title_to_body_match'] ?? 3),
            18
        );

        $score += min(
            count($matchedBodyTokens)
                * (float) ($scoreConfig['body_token_match'] ?? 0.8),
            12
        );

        if (
            $current->source
            && $candidate->source
            && Str::lower($current->source) !== Str::lower($candidate->source)
        ) {
            $score += (float) ($scoreConfig['different_source_bonus'] ?? 2);
        }

        $similarity = 0.0;

        if (
            $currentProfile['title_text'] !== ''
            && $candidateProfile['title_text'] !== ''
        ) {
            similar_text(
                $currentProfile['title_text'],
                $candidateProfile['title_text'],
                $similarity
            );
        }

        if ($similarity >= 30) {
            $maximumSimilarityBonus = (float) (
                $scoreConfig['maximum_title_similarity_bonus'] ?? 10
            );

            $score += min(
                max(0, ($similarity - 30) / 5),
                $maximumSimilarityBonus
            );
        }

        $score += $this->recencyBonus(
            $current->published_at ?: $current->created_at,
            $candidate->published_at ?: $candidate->created_at,
            (float) ($scoreConfig['maximum_recency_bonus'] ?? 8)
        );

        $matchedKeywords = array_values(array_unique(array_merge(
            $matchedEntities,
            array_slice($matchedTitleTokens, 0, 5),
            array_slice($titleToBodyTokens, 0, 3)
        )));

        /*
         * Không tạo quan hệ chỉ vì hai bài cùng category nhưng không có bất kỳ
         * từ khóa/thực thể thực tế nào liên quan.
         */
        if ($matchedKeywords === [] && ! $sameCategory) {
            return null;
        }

        if ($sameCategory && $matchedKeywords === []) {
            $score -= 8;
        }

        return [
            'news' => $candidate,
            'score' => $score,
            'matched_keywords' => array_slice($matchedKeywords, 0, 8),
            'reason' => $this->buildReason(
                $current,
                $sameCategory,
                $matchedEntities,
                $matchedTitleTokens,
                $titleToBodyTokens
            ),
        ];
    }

    private function recencyBonus(
        ?CarbonInterface $currentDate,
        ?CarbonInterface $candidateDate,
        float $maximumBonus
    ): float {
        if (! $currentDate || ! $candidateDate) {
            return 0;
        }

        $days = abs($currentDate->diffInDays($candidateDate));

        if ($days >= 240) {
            return 0;
        }

        return max(
            0,
            $maximumBonus * (1 - ($days / 240))
        );
    }

    /**
     * @param array<int, string> $matchedEntities
     * @param array<int, string> $matchedTitleTokens
     * @param array<int, string> $titleToBodyTokens
     */
    private function buildReason(
        News $current,
        bool $sameCategory,
        array $matchedEntities,
        array $matchedTitleTokens,
        array $titleToBodyTokens
    ): string {
        $parts = [];

        if ($sameCategory) {
            $parts[] = 'Cùng chuyên mục '
                . ($current->category?->name ?? 'tin tức');
        }

        if ($matchedEntities !== []) {
            $parts[] = 'Cùng chủ đề: '
                . implode(', ', array_slice($matchedEntities, 0, 4));
        }

        $titleKeywords = array_values(array_unique(array_merge(
            $matchedTitleTokens,
            $titleToBodyTokens
        )));

        if ($titleKeywords !== []) {
            $parts[] = 'Trùng từ khóa: '
                . implode(', ', array_slice($titleKeywords, 0, 5));
        }

        return Str::limit(
            implode(' · ', $parts) ?: 'Nội dung có mức độ liên quan cao',
            500,
            ''
        );
    }

    /**
     * @return array<int, int>
     */
    private function calculatePositions(
        int $blockCount,
        int $linkCount
    ): array {
        if ($linkCount <= 0) {
            return [];
        }

        $blockCount = max(1, $blockCount);
        $positions = [];

        for ($index = 1; $index <= $linkCount; $index++) {
            $position = (int) round(
                $index * ($blockCount + 1) / ($linkCount + 1)
            );

            if ($blockCount >= 3) {
                $position = max(2, $position);
            }

            $position = min($blockCount, max(1, $position));

            while (
                in_array($position, $positions, true)
                && $position < $blockCount
            ) {
                $position++;
            }

            $positions[] = $position;
        }

        return $positions;
    }

    private function normalizeText(?string $text): string
    {
        $text = html_entity_decode(
            strip_tags((string) $text),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = Str::lower(Str::ascii($text));
        $text = preg_replace('/[^a-z0-9]+/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $normalizedText): array
    {
        $stopWords = collect(config('news_related.stop_words', []))
            ->map(fn (string $word) => $this->normalizeText($word))
            ->filter()
            ->flip();

        return collect(explode(' ', $normalizedText))
            ->map(fn (string $token) => trim($token))
            ->filter(fn (string $token) => mb_strlen($token) >= 3)
            ->reject(fn (string $token) => $stopWords->has($token))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function extractEntities(string $normalizedText): array
    {
        $entities = [];

        foreach (config('news_related.entities', []) as $name => $aliases) {
            foreach ((array) $aliases as $alias) {
                $normalizedAlias = $this->normalizeText($alias);

                if ($normalizedAlias === '') {
                    continue;
                }

                $pattern = '/(?:^|\s)'
                    . preg_quote($normalizedAlias, '/')
                    . '(?:$|\s)/u';

                if (preg_match($pattern, $normalizedText)) {
                    $entities[] = (string) $name;
                    break;
                }
            }
        }

        return array_values(array_unique($entities));
    }
}