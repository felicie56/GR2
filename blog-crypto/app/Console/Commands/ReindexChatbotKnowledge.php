<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\News;
use App\Services\Chatbot\KnowledgeIndexService;
use Illuminate\Console\Command;
use Throwable;

class ReindexChatbotKnowledge extends Command
{
    protected $signature = 'chatbot:reindex
        {--type=all : all, blog hoặc news}
        {--id= : Chỉ đồng bộ một ID cụ thể}
        {--force : Upload lại dù content_hash không đổi}';

    protected $description =
        'Đồng bộ Blog đã duyệt và News vào OpenAI Vector Store';

    public function handle(
        KnowledgeIndexService $indexer
    ): int {
        $type = strtolower(
            trim((string) $this->option('type'))
        );

        $id = $this->option('id');

        $force = (bool) $this->option(
            'force'
        );

        if (
            ! in_array(
                $type,
                ['all', 'blog', 'news'],
                true
            )
        ) {
            $this->error(
                '--type chỉ nhận all, blog hoặc news.'
            );

            return self::FAILURE;
        }

        try {
            $vectorStoreId =
                $indexer->ensureVectorStore();

            $this->line(
                'Vector Store: ' . $vectorStoreId
            );

            if (
                $id !== null
                && $id !== ''
            ) {
                return $this->syncOne(
                    $indexer,
                    $type,
                    (int) $id,
                    $force
                );
            }

            $items = collect();

            if (
                in_array(
                    $type,
                    ['all', 'blog'],
                    true
                )
            ) {
                $blogs = BlogPost::query()
                    ->whereRaw(
                        'LOWER(status) = ?',
                        ['approved']
                    )
                    ->orderBy('id')
                    ->get()
                    ->map(
                        fn (BlogPost $post) => [
                            'type' => 'blog',
                            'model' => $post,
                        ]
                    );

                $items = $items->concat(
                    $blogs
                );
            }

            if (
                in_array(
                    $type,
                    ['all', 'news'],
                    true
                )
            ) {
                $news = News::query()
                    ->orderBy('id')
                    ->get()
                    ->map(
                        fn (News $article) => [
                            'type' => 'news',
                            'model' => $article,
                        ]
                    );

                $items = $items->concat(
                    $news
                );
            }

            if ($items->isEmpty()) {
                $this->warn(
                    'Không có nội dung phù hợp để lập chỉ mục.'
                );

                return self::SUCCESS;
            }

            $bar = $this->output
                ->createProgressBar(
                    $items->count()
                );

            $bar->start();

            $success = 0;
            $failed = 0;

            foreach ($items as $item) {
                try {
                    $indexer->sync(
                        $item['model'],
                        $force
                    );

                    $success++;
                } catch (Throwable $exception) {
                    $failed++;

                    $this->newLine();

                    $this->warn(
                        strtoupper($item['type'])
                        . ' #'
                        . $item['model']->getKey()
                        . ': '
                        . $exception->getMessage()
                    );
                }

                $bar->advance();
            }

            $bar->finish();

            $this->newLine(2);

            $this->info(
                "Thành công: {$success}"
            );

            if ($failed > 0) {
                $this->warn(
                    "Thất bại: {$failed}"
                );
            }

            return $failed === 0
                ? self::SUCCESS
                : self::FAILURE;
        } catch (Throwable $exception) {
            $this->error(
                $exception::class
            );

            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }

    private function syncOne(
        KnowledgeIndexService $indexer,
        string $type,
        int $id,
        bool $force
    ): int {
        if ($type === 'all') {
            $this->error(
                'Khi dùng --id, hãy chọn '
                . '--type=blog hoặc --type=news.'
            );

            return self::FAILURE;
        }

        $model = $type === 'blog'
            ? BlogPost::find($id)
            : News::find($id);

        if (! $model) {
            $this->error(
                "Không tìm thấy {$type} #{$id}."
            );

            return self::FAILURE;
        }

        $document = $indexer->sync(
            $model,
            $force
        );

        if (! $document) {
            $this->warn(
                'Nội dung không đủ điều kiện để lập chỉ mục.'
            );

            return self::SUCCESS;
        }

        $this->info(
            'Đồng bộ thành công.'
        );

        $this->table(
            ['Trường', 'Giá trị'],
            [
                [
                    'Document ID',
                    $document->id,
                ],
                [
                    'OpenAI file',
                    $document->openai_file_id,
                ],
                [
                    'Trạng thái',
                    $document->status,
                ],
                [
                    'URL',
                    $document->public_url,
                ],
            ]
        );

        return self::SUCCESS;
    }
}