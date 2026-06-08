<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        return view('news.index', compact('news', 'categories', 'search', 'categoryId'));
    }

    public function show(string $slug)
    {
        $newsItem = News::with([
                'category',
                'comments.user',
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

        return view('admin.news.index', compact('news', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = $this->allCategories();

        return view('admin.news.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateNews($request);

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

        return view('admin.news.edit', compact('article', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $article = News::findOrFail($id);

        $validated = $this->validateNews($request);

        if ($article->title !== $validated['title']) {
            $validated['slug'] = $this->uniqueSlug($validated['title'], $article->id);
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
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'url', 'max:1000'],
            'source' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'published_at' => ['nullable', 'date'],
        ]);
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