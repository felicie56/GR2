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

        $status = $request->query(
            'status',
            Comment::STATUS_PENDING
        );

        $search = trim(
            (string) $request->query('search', '')
        );

        $allowedTypes = [
            'all',
            'blog',
            'news',
        ];

        $allowedStatuses = [
            'all',
            Comment::STATUS_PENDING,
            Comment::STATUS_APPROVED,
            Comment::STATUS_REJECTED,
        ];

        if (! in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }

        if (! in_array($status, $allowedStatuses, true)) {
            $status = Comment::STATUS_PENDING;
        }

        $comments = Comment::with([
                'user',
                'blogPost',
                'news',
                'reviewer',
            ])
            ->when($type === 'blog', function ($query) {
                $query->whereNotNull('blog_post_id');
            })
            ->when($type === 'news', function ($query) {
                $query->whereNotNull('news_id');
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'content',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'user',
                            function ($userQuery) use ($search) {
                                $userQuery
                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        )
                        ->orWhereHas(
                            'blogPost',
                            function ($postQuery) use ($search) {
                                $postQuery->where(
                                    'title',
                                    'like',
                                    '%' . $search . '%'
                                );
                            }
                        )
                        ->orWhereHas(
                            'news',
                            function ($newsQuery) use ($search) {
                                $newsQuery->where(
                                    'title',
                                    'like',
                                    '%' . $search . '%'
                                );
                            }
                        );
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'type' => $type,
            'status' => $status,
            'search' => $search,

            'totalComments' => Comment::count(),

            'pendingComments' => Comment::pending()->count(),

            'approvedComments' => Comment::approved()->count(),

            'rejectedComments' => Comment::rejected()->count(),

            'blogComments' => Comment::whereNotNull(
                'blog_post_id'
            )->count(),

            'newsComments' => Comment::whereNotNull(
                'news_id'
            )->count(),
        ]);
    }

    public function approve(
        Request $request,
        Comment $comment
    ): RedirectResponse {
        $comment->update([
            'status' => Comment::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with(
            'success',
            'Bình luận đã được duyệt và được hiển thị công khai.'
        );
    }

    public function reject(
        Request $request,
        Comment $comment
    ): RedirectResponse {
        $comment->update([
            'status' => Comment::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with(
            'success',
            'Bình luận đã bị từ chối.'
        );
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with(
            'success',
            'Admin đã xóa bình luận.'
        );
    }
}