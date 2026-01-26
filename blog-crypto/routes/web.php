<?php

use App\Http\Controllers\CryptoController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Trang chủ = danh sách bài blog
Route::get('/', [BlogPostController::class, 'index'])->name('home');

// Blog list
Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index');

// ====== AUTHOR routes (PHẢI đặt trước /blog/{slug}) ======
Route::middleware(['auth', 'role:AUTHOR'])->group(function () {
    Route::get('/blog/create', [BlogPostController::class, 'create'])->name('blog.create');
    Route::post('/blog', [BlogPostController::class, 'store'])->name('blog.store');

    Route::get('/my/blogs', [BlogPostController::class, 'myPosts'])->name('blog.my');
    Route::get('/blog/{id}/edit', [BlogPostController::class, 'edit'])->name('blog.edit');
    Route::patch('/blog/{id}', [BlogPostController::class, 'update'])->name('blog.update');
});

// Blog detail (route động để CUỐI, tránh ăn mất /blog/create)
Route::get('/blog/{slug}', [BlogPostController::class, 'show'])->name('blog.show');

// ====== News public ======
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

// Dashboard
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');


// ====== Auth routes ======
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Comments
    Route::post('/blog/{postId}/comments', [CommentController::class, 'storeForBlog'])
        ->name('blog.comments.store');

    Route::post('/news/{newsId}/comments', [CommentController::class, 'storeForNews'])
        ->name('news.comments.store');

    // Like blog
    Route::post('/blog/{postId}/like', [ReactionController::class, 'toggleForBlog'])
        ->name('blog.like');
});

// ====== ADMIN: duyệt blog ======
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/admin/blogs/pending', [BlogPostController::class, 'pending'])->name('admin.blog.pending');
    Route::patch('/admin/blogs/{id}/approve', [BlogPostController::class, 'approve'])->name('admin.blog.approve');
    Route::patch('/admin/blogs/{id}/reject', [BlogPostController::class, 'reject'])->name('admin.blog.reject');
});

// ====== ADMIN: quản lý news ======
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/admin/news', [NewsController::class, 'adminIndex'])->name('admin.news.index');
    Route::get('/admin/news/create', [NewsController::class, 'create'])->name('admin.news.create');
    Route::post('/admin/news', [NewsController::class, 'store'])->name('admin.news.store');
    Route::get('/admin/news/{id}/edit', [NewsController::class, 'edit'])->name('admin.news.edit');
    Route::patch('/admin/news/{id}', [NewsController::class, 'update'])->name('admin.news.update');
    Route::delete('/admin/news/{id}', [NewsController::class, 'destroy'])->name('admin.news.destroy');
});

// Crypto
Route::get('/crypto', [CryptoController::class, 'index'])->name('crypto.index');

require __DIR__ . '/auth.php';
