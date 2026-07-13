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
    private const MAX_COMMENT_WORDS = 50;

    public function storeForBlog(
        Request $request,
        int $postId
    ): JsonResponse|RedirectResponse {
        $post = BlogPost::findOrFail($postId);

        $validated = $this->validateComment($request);
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

        $validated = $this->validateComment($request);

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

        $isOwner = (int) $comment->user_id === (int) auth()->id();

        $isAdmin = $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('ADMIN');

        abort_unless($isOwner || $isAdmin, 403);

        $comment->delete();

        return back()->with('success', 'Đã xóa bình luận.');
    }

    /**
     * Validate nội dung bình luận cho cả Blog và News.
     *
     * Dùng regex Unicode thay vì str_word_count() vì str_word_count()
     * không đếm chính xác tiếng Việt có dấu.
     */
    private function validateComment(Request $request): array
    {
        return $request->validate([
            'content' => [
                'required',
                'string',
                'min:2',
                'max:1000',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ): void {
                    $wordCount = $this->countWords((string) $value);

                    if ($wordCount > self::MAX_COMMENT_WORDS) {
                        $fail(
                            'Bình luận chỉ được tối đa '
                            . self::MAX_COMMENT_WORDS
                            . ' từ. Nội dung hiện có '
                            . $wordCount
                            . ' từ.'
                        );
                    }
                },
            ],
        ], [
            'content.required' => 'Bạn chưa nhập nội dung bình luận.',
            'content.string' => 'Nội dung bình luận không hợp lệ.',
            'content.min' => 'Bình luận phải có ít nhất 2 ký tự.',
            'content.max' => 'Bình luận quá dài. Vui lòng rút gọn nội dung.',
        ]);
    }

    private function countWords(string $content): int
    {
        preg_match_all(
            "/[\\p{L}\\p{N}]+(?:['’\\-][\\p{L}\\p{N}]+)*/u",
            trim(strip_tags($content)),
            $matches
        );

        return count($matches[0] ?? []);
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