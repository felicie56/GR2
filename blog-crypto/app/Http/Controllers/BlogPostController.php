<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    // Trang danh sách bài blog (feed)
    public function index()
    {
        // Lấy bài đã được duyệt, mới nhất lên trước
        $posts = BlogPost::where('status', 'approved')
            ->latest()
            ->paginate(10); // phân trang 10 bài / trang

        return view('blog.index', compact('posts'));
    }

    // Trang chi tiết bài blog
    public function show(string $slug)
{
    $post = BlogPost::where('slug', $slug)
    ->where('status', 'approved')
    ->with(['author', 'comments.user', 'reactions'])
    ->firstOrFail();


    return view('blog.show', compact('post'));
}

}
