<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
| Terminal chạy lịch:
| php artisan schedule:work
*/

Schedule::command('crypto:fetch-prices')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('news:fetch-rss --limit=3')
    ->everyMinute()
    ->withoutOverlapping();