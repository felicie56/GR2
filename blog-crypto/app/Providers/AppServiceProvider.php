<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\News;
use App\Observers\BlogPostObserver;
use App\Observers\NewsObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký các service vào container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Đăng ký Model Observer khi Laravel khởi động.
     */
    public function boot(): void
    {
        BlogPost::observe(
            BlogPostObserver::class
        );

        News::observe(
            NewsObserver::class
        );
    }
}