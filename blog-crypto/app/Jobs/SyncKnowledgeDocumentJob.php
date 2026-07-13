<?php

namespace App\Jobs;

use App\Models\AiKnowledgeDocument;
use App\Services\Chatbot\KnowledgeIndexService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncKnowledgeDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Chỉ đưa job vào queue
     * sau khi transaction DB commit.
     */
    public bool $afterCommit = true;

    /**
     * Thử lại tối đa ba lần.
     */
    public int $tries = 3;

    /**
     * Một tài liệu có tối đa 180 giây
     * để upload và index.
     */
    public int $timeout = 180;

    public function __construct(
        public readonly string $sourceType,
        public readonly int $sourceId,
        public readonly bool $force = false
    ) {
        $this->onQueue(
            'chatbot'
        );
    }

    public function handle(
        KnowledgeIndexService $indexer
    ): void {
        if (
            $this->sourceType
            === AiKnowledgeDocument::TYPE_BLOG
        ) {
            $blog = \App\Models\BlogPost::find(
                $this->sourceId
            );

            if ($blog) {
                $indexer->sync(
                    $blog,
                    $this->force
                );
            } else {
                $indexer->deleteSource(
                    $this->sourceType,
                    $this->sourceId
                );
            }

            return;
        }

        if (
            $this->sourceType
            === AiKnowledgeDocument::TYPE_NEWS
        ) {
            $news = \App\Models\News::find(
                $this->sourceId
            );

            if ($news) {
                $indexer->sync(
                    $news,
                    $this->force
                );
            } else {
                $indexer->deleteSource(
                    $this->sourceType,
                    $this->sourceId
                );
            }
        }
    }

    /**
     * Khoảng chờ trước mỗi lần retry.
     */
    public function backoff(): array
    {
        return [
            10,
            30,
            90,
        ];
    }

    /**
     * Ghi log nếu job thất bại hoàn toàn.
     */
    public function failed(
        Throwable $exception
    ): void {
        \Log::error(
            'Không thể đồng bộ tài liệu chatbot.',
            [
                'source_type' =>
                    $this->sourceType,

                'source_id' =>
                    $this->sourceId,

                'message' =>
                    $exception->getMessage(),
            ]
        );
    }
}