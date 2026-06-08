<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type', 'all');
        $search = $request->query('search');

        $comments = Comment::with(['user', 'blogPost', 'news'])
            ->when($type === 'blog', function ($query) {
                $query->whereNotNull('blog_post_id');
            })
            ->when($type === 'news', function ($query) {
                $query->whereNotNull('news_id');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('content', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('blogPost', function ($postQuery) use ($search) {
                            $postQuery->where('title', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('news', function ($newsQuery) use ($search) {
                            $newsQuery->where('title', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalComments = Comment::count();
        $blogComments = Comment::whereNotNull('blog_post_id')->count();
        $newsComments = Comment::whereNotNull('news_id')->count();

        return view('admin.comments.index', [
            'comments' => $comments,
            'type' => $type,
            'search' => $search,
            'totalComments' => $totalComments,
            'blogComments' => $blogComments,
            'newsComments' => $newsComments,
        ]);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Admin đã xóa bình luận.');
    }
}