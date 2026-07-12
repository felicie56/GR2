<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorApplication;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CryptoCoin;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingAuthorApplications = AuthorApplication::where('status', 'pending')->count();
        $approvedAuthorApplications = AuthorApplication::where('status', 'approved')->count();
        $rejectedAuthorApplications = AuthorApplication::where('status', 'rejected')->count();
        $totalAuthorApplications = AuthorApplication::count();

        $pendingBlogPosts = BlogPost::where('status', 'pending')->count();
        $approvedBlogPosts = BlogPost::whereIn('status', ['approved', 'published'])->count();
        $rejectedBlogPosts = BlogPost::where('status', 'rejected')->count();
        $totalBlogPosts = BlogPost::count();

        $totalUsers = User::count();
        $totalAuthors = $this->countUsersByRole('AUTHOR');
        $totalAdmins = $this->countUsersByRole('ADMIN');
        $totalComments = Comment::count();

        $totalNews = News::count();
        $totalCategories = Category::count();
        $totalCoins = CryptoCoin::count();

        $latestCryptoUpdate = $this->getLatestCryptoUpdate();

        return view('admin.dashboard', compact(
            'pendingAuthorApplications',
            'approvedAuthorApplications',
            'rejectedAuthorApplications',
            'totalAuthorApplications',
            'pendingBlogPosts',
            'approvedBlogPosts',
            'rejectedBlogPosts',
            'totalBlogPosts',
            'totalUsers',
            'totalAuthors',
            'totalAdmins',
            'totalComments',
            'totalNews',
            'totalCategories',
            'totalCoins',
            'latestCryptoUpdate'
        ));
    }

    private function countUsersByRole(string $role): int
    {
        $role = strtoupper($role);
        $userIds = collect();

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            $idsFromUsersTable = DB::table('users')
                ->whereRaw('UPPER(role) = ?', [$role])
                ->pluck('id');

            $userIds = $userIds->merge($idsFromUsersTable);
        }

        if (
            Schema::hasTable('users') &&
            Schema::hasTable('roles') &&
            Schema::hasTable('role_user') &&
            Schema::hasColumn('roles', 'id') &&
            Schema::hasColumn('roles', 'name') &&
            Schema::hasColumn('role_user', 'user_id') &&
            Schema::hasColumn('role_user', 'role_id')
        ) {
            $idsFromPivotTable = DB::table('users')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->whereRaw('UPPER(roles.name) = ?', [$role])
                ->pluck('users.id');

            $userIds = $userIds->merge($idsFromPivotTable);
        }

        return $userIds
            ->filter()
            ->unique()
            ->count();
    }

    private function getLatestCryptoUpdate(): mixed
    {
        if (! Schema::hasTable('crypto_prices')) {
            return null;
        }

        $candidateColumns = [
            'updated_at',
            'fetched_at',
            'recorded_at',
            'created_at',
            'last_updated_at',
        ];

        foreach ($candidateColumns as $column) {
            if (Schema::hasColumn('crypto_prices', $column)) {
                return DB::table('crypto_prices')
                    ->whereNotNull($column)
                    ->orderByDesc($column)
                    ->value($column);
            }
        }

        return null;
    }
}
