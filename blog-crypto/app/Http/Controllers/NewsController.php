<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // Danh sách tin tức
    public function index()
    {
        $news = News::latest('published_at')
            ->latest()
            ->paginate(10);

        return view('news.index', compact('news'));
    }

    // Chi tiết 1 tin tức
    public function show(string $slug)
{
    $article = News::where('slug', $slug)
        ->with(['comments.user'])
        ->firstOrFail();

    return view('news.show', compact('article'));
}

}
