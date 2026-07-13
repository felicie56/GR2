<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\NewsRelatedLinkService;
use Illuminate\Console\Command;

class GenerateNewsRelatedLinks extends Command
{
    protected $signature = 'news:generate-related-links
        {newsId? : ID của một bài tin cụ thể}
        {--all : Tạo lại liên kết cho toàn bộ tin tức}
        {--limit=4 : Số liên kết tối đa cho mỗi bài}';

    protected $description = 'Analyze news content and generate internal related-news links';

    public function handle(NewsRelatedLinkService $service): int
    {
        $newsId = $this->argument('newsId');
        $processAll = (bool) $this->option('all');
        $limit = max(1, min((int) $this->option('limit'), 8));

        if (! $processAll && ! $newsId) {
            $this->error(
                'Hãy truyền newsId hoặc sử dụng --all để xử lý toàn bộ tin.'
            );

            return self::FAILURE;
        }

        if ($newsId) {
            $article = News::find($newsId);

            if (! $article) {
                $this->error("Không tìm thấy bài tin có ID {$newsId}.");

                return self::FAILURE;
            }

            $links = $service->generateFor($article, $limit);

            $this->info(
                "Đã tạo {$links->count()} liên kết cho bài #{$article->id}: {$article->title}"
            );

            $this->displayLinks($links);

            return self::SUCCESS;
        }

        $total = News::count();

        if ($total === 0) {
            $this->warn('Chưa có tin tức nào trong database.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $createdLinks = 0;
        $failed = 0;

        News::query()
            ->orderBy('id')
            ->chunkById(50, function ($articles) use (
                $service,
                $limit,
                $bar,
                &$processed,
                &$createdLinks,
                &$failed
            ): void {
                foreach ($articles as $article) {
                    try {
                        $links = $service->generateFor($article, $limit);
                        $createdLinks += $links->count();
                    } catch (\Throwable $exception) {
                        $failed++;

                        $this->newLine();
                        $this->error(
                            "Lỗi bài #{$article->id}: {$exception->getMessage()}"
                        );
                    }

                    $processed++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info(
            "Hoàn tất. Đã xử lý {$processed}/{$total} bài, "
            . "tạo {$createdLinks} liên kết, lỗi {$failed} bài."
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function displayLinks($links): void
    {
        if ($links->isEmpty()) {
            $this->warn('Không tìm thấy bài cũ nào đạt ngưỡng liên quan.');
            return;
        }

        $this->table(
            ['Thứ tự', 'Bài liên quan', 'Điểm', 'Sau block', 'Lý do'],
            $links->map(function ($link): array {
                return [
                    $link->display_order,
                    $link->relatedNews?->title ?? 'Đã bị xóa',
                    number_format((float) $link->score, 2),
                    $link->paragraph_index,
                    $link->reason,
                ];
            })->all()
        );
    }
}