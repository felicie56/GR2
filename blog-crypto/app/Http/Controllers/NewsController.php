<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category');

        $categories = $this->activeCategories();

        $news = News::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('summary', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%')
                        ->orWhere('source', 'like', '%' . $search . '%')
                        ->orWhere('source_url', 'like', '%' . $search . '%');
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(9)
            ->withQueryString();

        return view('news.index', compact(
            'news',
            'categories',
            'search',
            'categoryId'
        ));
    }

    public function show(string $slug)
    {
        $newsItem = News::with([
                'category',
                'comments' => function ($query) {
                    $query->where('status', 'approved')
                        ->with('user')
                        ->latest();
                },
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('news.show', compact('newsItem'));
    }

    public function adminIndex(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category');

        $categories = $this->allCategories();

        $news = News::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('summary', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%')
                        ->orWhere('source', 'like', '%' . $search . '%')
                        ->orWhere('source_url', 'like', '%' . $search . '%');
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.news.index', compact(
            'news',
            'categories',
            'search',
            'categoryId'
        ));
    }

    public function create()
    {
        $categories = $this->allCategories();

        return view('admin.news.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateNews($request);

        $validated['content'] = $this->prepareRichText(
            $validated['content']
        );

        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['is_auto'] = false;
        $validated['fetched_at'] = null;

        News::create($validated);

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Đã tạo tin tức mới thành công.');
    }

    public function edit(int $id)
    {
        $article = News::findOrFail($id);
        $categories = $this->allCategories();

        return view('admin.news.edit', compact(
            'article',
            'categories'
        ));
    }

    public function update(Request $request, int $id)
    {
        $article = News::findOrFail($id);

        $validated = $this->validateNews($request);

        $validated['content'] = $this->prepareRichText(
            $validated['content']
        );

        if ($article->title !== $validated['title']) {
            $validated['slug'] = $this->uniqueSlug(
                $validated['title'],
                $article->id
            );
        }

        $article->update($validated);

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Đã cập nhật tin tức thành công.');
    }

    public function destroy(int $id)
    {
        $article = News::findOrFail($id);
        $article->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Đã xóa tin tức thành công.');
    }

    private function validateNews(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string', 'max:2000000'],
            'thumbnail' => ['nullable', 'url', 'max:1000'],
            'source' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * Làm sạch HTML được gửi từ CKEditor và kiểm tra bài có nội dung thật.
     */
    private function prepareRichText(string $html): string
    {
        $cleanHtml = $this->sanitizeRichText($html);

        $plainText = html_entity_decode(
            strip_tags($cleanHtml),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $plainText = preg_replace('/\x{00A0}/u', ' ', $plainText) ?? $plainText;
        $plainText = trim(preg_replace('/\s+/u', ' ', $plainText) ?? $plainText);

        if (mb_strlen($plainText) < 10) {
            throw ValidationException::withMessages([
                'content' => 'Nội dung phải có ít nhất 10 ký tự thực tế.',
            ]);
        }

        return $cleanHtml;
    }

    /**
     * Bộ lọc HTML gọn, không cần cài thêm Composer package.
     *
     * Chỉ giữ các thẻ CKEditor mà website đang dùng và loại bỏ:
     * - script/iframe/svg/form;
     * - event handler như onclick, onerror;
     * - URL javascript:, data:, vbscript:.
     */
    private function sanitizeRichText(string $html): string
    {
        $html = trim($html);

        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? '';

        $html = preg_replace(
            '#<(script|style|iframe|object|embed|svg|math|form|input|button|textarea|select|option|video|audio|canvas)\b[^>]*>.*?</\1\s*>#is',
            '',
            $html
        ) ?? '';

        $allowedTags = '<p><br><h2><h3><h4><strong><b><em><i><u><s>'
            . '<blockquote><ul><ol><li><a><figure><figcaption><img>';

        $html = strip_tags($html, $allowedTags);

        $html = preg_replace_callback(
            '/<([a-z0-9]+)\b([^>]*)>/i',
            function (array $matches): string {
                $tag = strtolower($matches[1]);
                $attributes = $this->extractHtmlAttributes($matches[2] ?? '');

                return $this->rebuildAllowedOpeningTag($tag, $attributes);
            },
            $html
        ) ?? '';

        return trim($html);
    }

    private function extractHtmlAttributes(string $rawAttributes): array
    {
        $attributes = [];

        preg_match_all(
            '/([a-zA-Z_:][a-zA-Z0-9:._-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))/',
            $rawAttributes,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $name = strtolower($match[1]);
            $value = $match[2] !== ''
                ? $match[2]
                : ($match[3] !== '' ? $match[3] : ($match[4] ?? ''));

            $attributes[$name] = html_entity_decode(
                $value,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        }

        return $attributes;
    }

    private function rebuildAllowedOpeningTag(
        string $tag,
        array $attributes
    ): string {
        if ($tag === 'br') {
            return '<br>';
        }

        if ($tag === 'a') {
            $href = $this->sanitizeContentUrl(
                $attributes['href'] ?? null,
                allowMailto: true
            );

            if (! $href) {
                return '<a>';
            }

            $output = '<a href="' . e($href) . '"';

            if (! empty($attributes['title'])) {
                $output .= ' title="' . e($attributes['title']) . '"';
            }

            if (($attributes['target'] ?? null) === '_blank') {
                $output .= ' target="_blank" rel="noopener noreferrer"';
            }

            return $output . '>';
        }

        if ($tag === 'img') {
            $src = $this->sanitizeContentUrl($attributes['src'] ?? null);

            if (! $src) {
                return '';
            }

            $output = '<img src="' . e($src) . '"';

            if (isset($attributes['alt'])) {
                $output .= ' alt="' . e($attributes['alt']) . '"';
            } else {
                $output .= ' alt=""';
            }

            foreach (['width', 'height'] as $dimension) {
                $value = $attributes[$dimension] ?? null;

                if ($value !== null && preg_match('/^\d{1,5}$/', $value)) {
                    $output .= ' ' . $dimension . '="' . e($value) . '"';
                }
            }

            return $output . '>';
        }

        if ($tag === 'figure') {
            $allowedClasses = [
                'image',
                'image-style-side',
                'image-style-align-left',
                'image-style-align-right',
                'image-style-block-align-left',
                'image-style-block-align-right',
            ];

            $classes = preg_split(
                '/\s+/',
                trim($attributes['class'] ?? ''),
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            $classes = array_values(array_intersect($classes, $allowedClasses));

            if (! in_array('image', $classes, true)) {
                array_unshift($classes, 'image');
            }

            return '<figure class="' . e(implode(' ', $classes)) . '">';
        }

        return '<' . $tag . '>';
    }

    private function sanitizeContentUrl(
        ?string $url,
        bool $allowMailto = false
    ): ?string {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $normalized = strtolower(
            preg_replace('/[\x00-\x20]+/', '', $url) ?? $url
        );

        if (Str::startsWith($normalized, [
            'javascript:',
            'data:',
            'vbscript:',
            'file:',
        ])) {
            return null;
        }

        if ($allowMailto && Str::startsWith($normalized, 'mailto:')) {
            return $url;
        }

        if (Str::startsWith($url, ['/', './', '../', '#'])) {
            return $url;
        }

        if (preg_match('#^https?://#i', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL)
                ? $url
                : null;
        }

        return null;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        if (! $baseSlug) {
            $baseSlug = 'news-' . Str::random(8);
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            News::where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function activeCategories()
    {
        $query = Category::query();

        if (Schema::hasColumn('categories', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    private function allCategories()
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }
}