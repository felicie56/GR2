<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    // Toggle like cho một bài blog
    public function toggleForBlog(int $postId)
    {
        $user = Auth::user();

        $post = BlogPost::where('id', $postId)
            ->where('status', 'approved')
            ->firstOrFail();

        // Tìm xem user đã like bài này chưa
        $existing = Reaction::where('user_id', $user->id)
            ->where('blog_post_id', $post->id)
            ->first();

        if ($existing) {
            // Đã like rồi -> bỏ like
            $existing->delete();
            $message = 'Đã bỏ thích bài viết.';
        } else {
            // Chưa like -> tạo reaction
            Reaction::create([
                'user_id'      => $user->id,
                'blog_post_id' => $post->id,
                'type'         => 'like',
            ]);
            $message = 'Đã thích bài viết.';
        }

        return redirect()
            ->route('blog.show', $post->slug)
            ->with('success', $message);
    }
}
