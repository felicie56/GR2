<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsController extends Controller
{
    // Public: danh sách tin tức
    public function index()
    {
        $news = News::latest('published_at')
            ->latest()
            ->paginate(10);

        return view('news.index', compact('news'));
    }

    // Public: chi tiết tin tức
    public function show(string $slug)
    {
        $article = News::where('slug', $slug)
            ->with(['comments.user'])
            ->firstOrFail();

        return view('news.show', compact('article'));
    }

    // ================== ADMIN CRUD ==================

    // Admin: list
    public function adminIndex()
    {
        $news = News::latest()
            ->paginate(10);

        return view('admin.news.index', compact('news'));
    }

    // Admin: form create
    public function create()
    {
        return view('admin.news.create');
    }

    // Admin: store
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'url'],
            'source' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'content' => ['required', 'string'],
        ]);

        $slug = Str::slug($validated['title']);
        $baseSlug = $slug;
        $i = 2;
        while (News::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        News::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'thumbnail' => $validated['thumbnail'] ?? null,
            'source' => $validated['source'] ?? null,
            'published_at' => isset($validated['published_at'])
                ? Carbon::parse($validated['published_at'])
                : null,
            'content' => $validated['content'],
        ]);

        return redirect()->route('admin.news.index')->with('success', 'Đã tạo tin tức.');
    }

    // Admin: form edit
    public function edit($id)
    {
        $article = News::findOrFail($id);
        return view('admin.news.edit', compact('article'));
    }

    // Admin: update
    public function update(Request $request, $id)
    {
        $article = News::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'url'],
            'source' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'content' => ['required', 'string'],
        ]);

        // Nếu đổi title thì cập nhật slug (và đảm bảo unique)
        $slug = $article->slug;
        if ($validated['title'] !== $article->title) {
            $slug = Str::slug($validated['title']);
            $baseSlug = $slug;
            $i = 2;
            while (News::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }
        }

        $article->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'thumbnail' => $validated['thumbnail'] ?? null,
            'source' => $validated['source'] ?? null,
            'published_at' => isset($validated['published_at'])
                ? Carbon::parse($validated['published_at'])
                : null,
            'content' => $validated['content'],
        ]);

        return redirect()->route('admin.news.index')->with('success', 'Đã cập nhật tin tức.');
    }

    // Admin: delete
    public function destroy($id)
    {
        $article = News::findOrFail($id);
        $article->delete();

        return redirect()->route('admin.news.index')->with('success', 'Đã xoá tin tức.');
    }
}
