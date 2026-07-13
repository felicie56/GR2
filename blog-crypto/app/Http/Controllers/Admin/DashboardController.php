<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorApplication;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();

        $totalAuthors = 0;
        $totalAdmins = 0;

        if (Schema::hasTable('role_user') && Schema::hasTable('roles')) {
            $totalAuthors = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('roles.name', 'AUTHOR')
                ->distinct('role_user.user_id')
                ->count('role_user.user_id');

            $totalAdmins = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('roles.name', 'ADMIN')
                ->distinct('role_user.user_id')
                ->count('role_user.user_id');
        }

        $pendingAuthorApplications = AuthorApplication::where('status', 'pending')->count();
        $approvedAuthorApplications = AuthorApplication::where('status', 'approved')->count();
        $rejectedAuthorApplications = AuthorApplication::where('status', 'rejected')->count();

        $pendingBlogPosts = BlogPost::where('status', 'pending')->count();
        $approvedBlogPosts = BlogPost::where('status', 'approved')->count();
        $rejectedBlogPosts = BlogPost::where('status', 'rejected')->count();
        $totalBlogPosts = BlogPost::count();

        $totalNews = News::count();
        $totalCategories = Schema::hasTable('categories') ? Category::count() : 0;
        $totalComments = Schema::hasTable('comments') ? Comment::count() : 0;

        $totalCryptoCoins = Schema::hasTable('crypto_coins')
            ? DB::table('crypto_coins')->count()
            : 0;

        $latestCryptoPriceAt = null;

        if (Schema::hasTable('crypto_prices')) {
            $possibleTimeColumns = [
                'created_at',
                'updated_at',
                'fetched_at',
                'recorded_at',
                'price_time',
                'timestamp',
            ];

            foreach ($possibleTimeColumns as $column) {
                if (Schema::hasColumn('crypto_prices', $column)) {
                    $latestCryptoPriceAt = DB::table('crypto_prices')->max($column);
                    break;
                }
            }
        }

        $latestAuthorApplications = AuthorApplication::with('user')
            ->latest()
            ->take(5)
            ->get();

        $latestPendingBlogPosts = BlogPost::with(['author', 'category'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $latestNews = News::with('category')
            ->latest()
            ->take(5)
            ->get();

        $latestUsers = User::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalAuthors' => $totalAuthors,
            'totalAdmins' => $totalAdmins,

            'pendingAuthorApplications' => $pendingAuthorApplications,
            'approvedAuthorApplications' => $approvedAuthorApplications,
            'rejectedAuthorApplications' => $rejectedAuthorApplications,

            'pendingBlogPosts' => $pendingBlogPosts,
            'approvedBlogPosts' => $approvedBlogPosts,
            'rejectedBlogPosts' => $rejectedBlogPosts,
            'totalBlogPosts' => $totalBlogPosts,

            'totalNews' => $totalNews,
            'totalCategories' => $totalCategories,
            'totalComments' => $totalComments,

            'totalCryptoCoins' => $totalCryptoCoins,
            'latestCryptoPriceAt' => $latestCryptoPriceAt,

            'latestAuthorApplications' => $latestAuthorApplications,
            'latestPendingBlogPosts' => $latestPendingBlogPosts,
            'latestNews' => $latestNews,
            'latestUsers' => $latestUsers,
        ]);
    }
}