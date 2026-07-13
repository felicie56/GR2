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
| Local:
| php artisan schedule:work
|
| Production:
| Cron gọi php artisan schedule:run mỗi phút.
*/

Schedule::command('crypto:fetch-prices')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('news:fetch-rss --limit=3')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('news:generate-related-links --all --limit=4')
    ->dailyAt('03:20')
    ->withoutOverlapping();

/*
 * Observers đã tự đưa Blog/News mới vào queue chatbot.
 * Lệnh này chạy dự phòng mỗi đêm để đồng bộ lại tài liệu bị thiếu hoặc lỗi.
 */
Schedule::command('chatbot:reindex --type=all')
    ->dailyAt('04:10')
    ->withoutOverlapping();