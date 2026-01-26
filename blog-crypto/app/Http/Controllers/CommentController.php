<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\News;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Comment cho bài blog
    public function storeForBlog(Request $request, int $postId)
    {
        $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post = BlogPost::where('id', $postId)
            ->where('status', 'approved')
            ->firstOrFail();

        Comment::create([
            'user_id'      => Auth::id(),
            'blog_post_id' => $post->id,
            'news_id'      => null,
            'content'      => $request->input('content'),
        ]);

        return redirect()
            ->route('blog.show', $post->slug)
            ->with('success', 'Đã gửi bình luận.');
    }

    // Comment cho tin tức
    public function storeForNews(Request $request, int $newsId)
    {
        $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $news = News::findOrFail($newsId);

        Comment::create([
            'user_id'      => Auth::id(),
            'blog_post_id' => null,
            'news_id'      => $news->id,
            'content'      => $request->input('content'),
        ]);

        return redirect()
            ->route('news.show', $news->slug)
            ->with('success', 'Đã gửi bình luận.');
    }
}
