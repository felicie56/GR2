<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Support\Str;
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
public function create()
{
    return view('blog.create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'content' => ['required', 'string'],
        'thumbnail' => ['nullable', 'url'],
    ]);

    $slug = Str::slug($validated['title']);

    // nếu trùng slug thì thêm hậu tố
    $baseSlug = $slug;
    $i = 2;
    while (BlogPost::where('slug', $slug)->exists()) {
        $slug = $baseSlug . '-' . $i;
        $i++;
    }

    BlogPost::create([
        'user_id' => $request->user()->id,
        'title' => $validated['title'],
        'slug' => $slug,
        'content' => $validated['content'],
        'thumbnail' => $validated['thumbnail'] ?? null,
        'status' => 'pending', // đúng nghiệp vụ: author đăng => chờ duyệt
    ]);

    return redirect()->route('blog.my')->with('success', 'Đã gửi bài viết. Bài đang chờ duyệt.');
}

public function myPosts(Request $request)
{
    $posts = BlogPost::where('user_id', $request->user()->id)
        ->latest()
        ->paginate(10);

    return view('blog.my', compact('posts'));
}

public function pending()
{
    $posts = BlogPost::where('status', 'pending')
        ->with('author')
        ->latest()
        ->paginate(10);

    return view('admin.blogs.pending', compact('posts'));
}

public function approve($id)
{
    $post = BlogPost::findOrFail($id);
    $post->status = 'approved';
    $post->save();

    return back()->with('success', 'Đã duyệt bài.');
}

public function reject($id)
{
    $post = BlogPost::findOrFail($id);
    $post->status = 'rejected';
    $post->save();

    return back()->with('success', 'Đã từ chối bài.');
}

}
