<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReactionController extends Controller
{
    public function toggleForBlog(Request $request, int $postId)
    {
        $post = BlogPost::findOrFail($postId);
        $userId = auth()->id();

        $blogForeignKey = $this->blogReactionForeignKey();

        $reaction = Reaction::where('user_id', $userId)
            ->where($blogForeignKey, $post->id)
            ->first();

        $liked = false;

        if ($reaction) {
            $reaction->delete();
            $liked = false;
        } else {
            Reaction::create([
                'user_id' => $userId,
                $blogForeignKey => $post->id,
            ]);

            $liked = true;
        }

        $likeCount = Reaction::where($blogForeignKey, $post->id)->count();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'like_count' => $likeCount,
                'message' => $liked ? 'Đã thích bài viết.' : 'Đã bỏ thích bài viết.',
            ]);
        }

        return back()->with('success', $liked ? 'Đã thích bài viết.' : 'Đã bỏ thích bài viết.');
    }

    private function blogReactionForeignKey(): string
    {
        if (Schema::hasColumn('reactions', 'blog_post_id')) {
            return 'blog_post_id';
        }

        if (Schema::hasColumn('reactions', 'post_id')) {
            return 'post_id';
        }

        return 'blog_post_id';
    }
}