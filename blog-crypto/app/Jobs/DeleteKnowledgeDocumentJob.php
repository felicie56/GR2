<?php

namespace App\Jobs;

use App\Services\Chatbot\KnowledgeIndexService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteKnowledgeDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $afterCommit = true;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly string $sourceType,
        public readonly int $sourceId
    ) {
        $this->onQueue(
            'chatbot'
        );
    }

    public function handle(
        KnowledgeIndexService $indexer
    ): void {
        $indexer->deleteSource(
            $this->sourceType,
            $this->sourceId
        );
    }

    public function backoff(): array
    {
        return [
            10,
            30,
            90,
        ];
    }
}