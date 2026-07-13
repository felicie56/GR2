<?php

namespace App\Console\Commands;

use App\Services\Chatbot\KnowledgeIndexService;
use Illuminate\Console\Command;
use Throwable;

class SetupChatbotVectorStore extends Command
{
    protected $signature = 'chatbot:setup-vector-store
        {--force : Tạo Vector Store mới ngay cả khi đã có ID}';

    protected $description =
        'Tạo hoặc kiểm tra OpenAI Vector Store cho dữ liệu Blog và News';

    public function handle(
        KnowledgeIndexService $indexer
    ): int {
        try {
            $id = $indexer->ensureVectorStore(
                (bool) $this->option('force')
            );

            $this->info(
                'Vector Store đã sẵn sàng.'
            );

            $this->line(
                'ID: ' . $id
            );

            $this->newLine();

            $this->comment(
                'Hệ thống đã lưu ID cục bộ trong storage. '
                . 'Bạn cũng có thể đặt OPENAI_VECTOR_STORE_ID='
                . $id
                . ' trong .env.'
            );

            return self::SUCCESS;
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
}