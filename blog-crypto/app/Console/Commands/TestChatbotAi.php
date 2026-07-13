<?php

namespace App\Console\Commands;

use App\Models\ChatbotSession;
use App\Services\Chatbot\ChatbotTurnService;
use App\Services\Chatbot\ConversationSessionService;
use Illuminate\Console\Command;
use Throwable;

class TestChatbotAi extends Command
{
    protected $signature = 'chatbot:test-openai
        {message : Nội dung muốn gửi tới chatbot}
        {--session= : UUID của phiên cũ để kiểm tra nhớ ngữ cảnh}';

    protected $description =
        'Kiểm tra kết nối OpenAI Responses API và bộ nhớ hội thoại';

    public function handle(
        ChatbotTurnService $turns,
        ConversationSessionService $sessions
    ): int {
        try {
            $sessionUuid = trim(
                (string) $this->option(
                    'session'
                )
            );

            if ($sessionUuid !== '') {
                $session =
                    ChatbotSession::query()
                        ->where(
                            'uuid',
                            $sessionUuid
                        )
                        ->first();

                if (! $session) {
                    $this->error(
                        'Không tìm thấy phiên có UUID: '
                        . $sessionUuid
                    );

                    return self::FAILURE;
                }
            } else {
                $session =
                    $sessions->startNewForCli();
            }

            $this->newLine();
            $this->line(
                '<fg=cyan>Session UUID:</> '
                . $session->uuid
            );

            $this->line(
                '<fg=gray>Đang gửi tới OpenAI...</>'
            );

            $result = $turns->ask(
                session: $session,
                message: (string) $this->argument(
                    'message'
                )
            );

            $this->newLine();
            $this->info('Câu trả lời:');
            $this->line(
                (string) $result['answer']
            );

            $usage = $result['usage'] ?? [];

            $this->newLine();
            $this->table(
                ['Thông tin', 'Giá trị'],
                [
                    [
                        'Model',
                        $result['model'] ?? '-',
                    ],
                    [
                        'Response ID',
                        $result['response_id']
                            ?? '-',
                    ],
                    [
                        'Input tokens',
                        $usage['input_tokens']
                            ?? '-',
                    ],
                    [
                        'Cached input tokens',
                        $usage[
                            'cached_input_tokens'
                        ] ?? '-',
                    ],
                    [
                        'Output tokens',
                        $usage['output_tokens']
                            ?? '-',
                    ],
                    [
                        'Total tokens',
                        $usage['total_tokens']
                            ?? '-',
                    ],
                    [
                        'Latency',
                        ($result['latency_ms']
                            ?? 0) . ' ms',
                    ],
                ]
            );

            $this->newLine();
            $this->comment(
                'Để kiểm tra nhớ ngữ cảnh, gửi câu tiếp theo bằng:'
            );

            $this->line(
                'php artisan chatbot:test-openai '
                . '"Câu hỏi tiếp theo" '
                . '--session='
                . $session->uuid
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();
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