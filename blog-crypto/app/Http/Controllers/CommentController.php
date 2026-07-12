<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CommentController extends Controller
{
    public function storeForBlog(
        Request $request,
        int $postId
    ): JsonResponse|RedirectResponse {
        $post = BlogPost::findOrFail($postId);

        $validated = $request->validate([
            'content' => [
                'required',
                'string',
                'min:2',
                'max:2000',
            ],
        ]);

        $blogForeignKey = $this->blogCommentForeignKey();

        Comment::create([
            'user_id' => $request->user()->id,
            $blogForeignKey => $post->id,
            'content' => $validated['content'],
            'status' => Comment::STATUS_PENDING,
        ]);

        $approvedCommentCount = Comment::query()
            ->where($blogForeignKey, $post->id)
            ->approved()
            ->count();

        $message = 'Bình luận đã được gửi và đang chờ admin duyệt.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'comment_count' => $approvedCommentCount,
            ], 201);
        }

        return back()->with('success', $message);
    }

    public function storeForNews(
        Request $request,
        int $newsId
    ): JsonResponse|RedirectResponse {
        $news = News::findOrFail($newsId);

        $validated = $request->validate([
            'content' => [
                'required',
                'string',
                'min:2',
                'max:2000',
            ],
        ]);

        Comment::create([
            'user_id' => $request->user()->id,
            'news_id' => $news->id,
            'content' => $validated['content'],
            'status' => Comment::STATUS_PENDING,
        ]);

        $approvedCommentCount = Comment::query()
            ->where('news_id', $news->id)
            ->approved()
            ->count();

        $message = 'Bình luận đã được gửi và đang chờ admin duyệt.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'comment_count' => $approvedCommentCount,
            ], 201);
        }

        return back()->with('success', $message);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $user = auth()->user();

        $isOwner = $comment->user_id === auth()->id();

        $isAdmin = $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('ADMIN');

        abort_unless($isOwner || $isAdmin, 403);

        $comment->delete();

        return back()->with('success', 'Đã xóa bình luận.');
    }

    private function blogCommentForeignKey(): string
    {
        if (Schema::hasColumn('comments', 'blog_post_id')) {
            return 'blog_post_id';
        }

        if (Schema::hasColumn('comments', 'post_id')) {
            return 'post_id';
        }

        return 'blog_post_id';
    }
}