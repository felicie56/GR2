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

// Trang danh sách blog (nếu muốn có /blog riêng)
Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index');

// Trang chi tiết 1 bài blog theo slug
Route::get('/blog/{slug}', [BlogPostController::class, 'show'])->name('blog.show');

// Tin tức
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gửi comment cho blog
    Route::post('/blog/{postId}/comments', [CommentController::class, 'storeForBlog'])
        ->name('blog.comments.store');

    // Gửi comment cho news
    Route::post('/news/{newsId}/comments', [CommentController::class, 'storeForNews'])
        ->name('news.comments.store');
            // ... các route profile, comments ở trên

    // Toggle like cho bài blog
    Route::post('/blog/{postId}/like', [ReactionController::class, 'toggleForBlog'])
        ->name('blog.like');

});

// Trang giá crypto
Route::get('/crypto', [CryptoController::class, 'index'])->name('crypto.index');


require __DIR__.'/auth.php';
