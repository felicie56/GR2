<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CommentController extends Controller
{
    public function storeForBlog(Request $request, int $postId)
    {
        $post = BlogPost::findOrFail($postId);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $blogForeignKey = $this->blogCommentForeignKey();

        $comment = Comment::create([
            'user_id' => auth()->id(),
            $blogForeignKey => $post->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        $commentCount = Comment::where($blogForeignKey, $post->id)->count();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bình luận đã được gửi.',
                'comment_count' => $commentCount,
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_name' => $comment->user?->name ?? 'Người dùng',
                    'created_at' => $comment->created_at?->format('d/m/Y H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Bình luận đã được gửi.');
    }

    public function storeForNews(Request $request, int $newsId)
    {
        $news = News::findOrFail($newsId);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'news_id' => $news->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        $commentCount = Comment::where('news_id', $news->id)->count();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bình luận đã được gửi.',
                'comment_count' => $commentCount,
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_name' => $comment->user?->name ?? 'Người dùng',
                    'created_at' => $comment->created_at?->format('d/m/Y H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Bình luận đã được gửi.');
    }

    public function destroy(Comment $comment)
    {
        $user = auth()->user();

        $isOwner = $comment->user_id === auth()->id();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('ADMIN');

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