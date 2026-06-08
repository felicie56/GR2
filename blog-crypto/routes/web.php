<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CryptoController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthorApplicationController;
use App\Http\Controllers\ChatbotController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthorApplicationController as AdminAuthorApplicationController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [BlogPostController::class, 'index'])
    ->name('home');

Route::get('/blog', [BlogPostController::class, 'index'])
    ->name('blog.index');

Route::get('/news', [NewsController::class, 'index'])
    ->name('news.index');

Route::get('/news/{slug}', [NewsController::class, 'show'])
    ->name('news.show');

Route::get('/crypto', [CryptoController::class, 'index'])
    ->name('crypto.index');

Route::get('/crypto/{symbol}', [CryptoController::class, 'show'])
    ->where('symbol', '[A-Za-z0-9\-_]+')
    ->name('crypto.show');

Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])
    ->name('chatbot.ask');


/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECT
|--------------------------------------------------------------------------
| Sau khi login:
| - ADMIN sẽ đi tới /admin/dashboard
| - USER / AUTHOR sẽ về trang chủ
*/

Route::get('/dashboard', function () {
    $user = auth()->user();

    if (
        $user
        && method_exists($user, 'hasRole')
        && $user->hasRole('ADMIN')
        && Route::has('admin.dashboard')
    ) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Author Application
    |--------------------------------------------------------------------------
    */

    Route::get('/author/apply', [AuthorApplicationController::class, 'create'])
        ->name('author.apply.create');

    Route::post('/author/apply', [AuthorApplicationController::class, 'store'])
        ->name('author.apply.store');

    Route::post('/author-applications/{application}/mark-seen', [AuthorApplicationController::class, 'markSeen'])
        ->whereNumber('application')
        ->name('author.applications.mark-seen');


    /*
    |--------------------------------------------------------------------------
    | Blog Review Notification
    |--------------------------------------------------------------------------
    */

    Route::post('/my/blogs/{post}/review-seen', [BlogPostController::class, 'markReviewSeen'])
        ->whereNumber('post')
        ->name('blog.review.mark-seen');


    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

    Route::post('/blog/{postId}/comments', [CommentController::class, 'storeForBlog'])
        ->whereNumber('postId')
        ->name('blog.comments.store');

    Route::post('/news/{newsId}/comments', [CommentController::class, 'storeForNews'])
        ->whereNumber('newsId')
        ->name('news.comments.store');

    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->whereNumber('comment')
        ->name('comments.destroy');


    /*
    |--------------------------------------------------------------------------
    | Reactions
    |--------------------------------------------------------------------------
    */

    Route::post('/blog/{postId}/like', [ReactionController::class, 'toggleForBlog'])
        ->whereNumber('postId')
        ->name('blog.like');
});


/*
|--------------------------------------------------------------------------
| AUTHOR ROUTES
|--------------------------------------------------------------------------
| Phải đặt trước /blog/{slug}, để tránh /blog/create bị hiểu là slug.
*/

Route::middleware(['auth', 'role:AUTHOR'])->group(function () {
    Route::get('/blog/create', [BlogPostController::class, 'create'])
        ->name('blog.create');

    Route::post('/blog', [BlogPostController::class, 'store'])
        ->name('blog.store');

    Route::get('/my/blogs', [BlogPostController::class, 'myPosts'])
        ->name('blog.my');

    Route::get('/blog/{post}/edit', [BlogPostController::class, 'edit'])
        ->whereNumber('post')
        ->name('blog.edit');

    Route::patch('/blog/{post}', [BlogPostController::class, 'update'])
        ->whereNumber('post')
        ->name('blog.update');

    Route::delete('/blog/{post}', [BlogPostController::class, 'destroy'])
        ->whereNumber('post')
        ->name('blog.destroy');

        Route::delete('/blog/images/{image}', [BlogPostController::class, 'deleteImage'])
    ->whereNumber('image')
    ->name('blog.images.destroy');
});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/admin', function () {
        return redirect()->route('admin.dashboard');
    })->name('admin.home');

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin Comments
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/comments', [AdminCommentController::class, 'index'])
        ->name('admin.comments.index');

    Route::delete('/admin/comments/{comment}', [AdminCommentController::class, 'destroy'])
        ->whereNumber('comment')
        ->name('admin.comments.destroy');


    /*
    |--------------------------------------------------------------------------
    | Admin Author Applications
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/author-applications', [AdminAuthorApplicationController::class, 'index'])
        ->name('admin.author-applications.index');

    Route::get('/admin/author-applications/{application}', [AdminAuthorApplicationController::class, 'show'])
        ->whereNumber('application')
        ->name('admin.author-applications.show');

    Route::patch('/admin/author-applications/{application}/approve', [AdminAuthorApplicationController::class, 'approve'])
        ->whereNumber('application')
        ->name('admin.author-applications.approve');

    Route::patch('/admin/author-applications/{application}/reject', [AdminAuthorApplicationController::class, 'reject'])
        ->whereNumber('application')
        ->name('admin.author-applications.reject');


    /*
    |--------------------------------------------------------------------------
    | Admin Blog Review
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/blogs/pending', [BlogPostController::class, 'pending'])
        ->name('admin.blog.pending');

    Route::patch('/admin/blogs/{post}/approve', [BlogPostController::class, 'approve'])
        ->whereNumber('post')
        ->name('admin.blog.approve');

    Route::patch('/admin/blogs/{post}/reject', [BlogPostController::class, 'reject'])
        ->whereNumber('post')
        ->name('admin.blog.reject');


    /*
    |--------------------------------------------------------------------------
    | Admin News
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/news', [NewsController::class, 'adminIndex'])
        ->name('admin.news.index');

    Route::get('/admin/news/create', [NewsController::class, 'create'])
        ->name('admin.news.create');

    Route::post('/admin/news', [NewsController::class, 'store'])
        ->name('admin.news.store');

    Route::get('/admin/news/{id}/edit', [NewsController::class, 'edit'])
        ->whereNumber('id')
        ->name('admin.news.edit');

    Route::patch('/admin/news/{id}', [NewsController::class, 'update'])
        ->whereNumber('id')
        ->name('admin.news.update');

    Route::delete('/admin/news/{id}', [NewsController::class, 'destroy'])
        ->whereNumber('id')
        ->name('admin.news.destroy');
});


/*
|--------------------------------------------------------------------------
| BLOG DETAIL
|--------------------------------------------------------------------------
| Route động để cuối, tránh ăn mất /blog/create, /blog/{post}/edit.
*/

Route::get('/blog/{slug}', [BlogPostController::class, 'show'])
    ->name('blog.show');


/*
|--------------------------------------------------------------------------
| BREEZE AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';