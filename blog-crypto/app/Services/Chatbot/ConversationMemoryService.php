<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use Throwable;

class ConversationMemoryService
{
    public function __construct(
        private readonly OpenAiResponsesClient $openAi
    ) {
    }

    public function compactIfNeeded(ChatbotSession $session): void
    {
        if (! (bool) config('chatbot.conversation.auto_summarize', true)) {
            return;
        }

        $threshold = max(6, (int) config(
            'chatbot.conversation.summary_after_messages',
            16
        ));

        $completedCount = $session->messages()
            ->where('status', ChatbotMessage::STATUS_COMPLETED)
            ->whereIn('role', [
                ChatbotMessage::ROLE_USER,
                ChatbotMessage::ROLE_ASSISTANT,
            ])
            ->count();

        if ($completedCount < $threshold) {
            return;
        }

        if (
            $session->context_compacted_at
            && $session->context_compacted_at->gt(now()->subMinutes(10))
        ) {
            return;
        }

        $recentLimit = max(4, (int) config(
            'chatbot.conversation.recent_message_limit',
            8
        ));

        $messages = $session->messages()
            ->where('status', ChatbotMessage::STATUS_COMPLETED)
            ->whereIn('role', [
                ChatbotMessage::ROLE_USER,
                ChatbotMessage::ROLE_ASSISTANT,
            ])
            ->latest('id')
            ->limit($threshold)
            ->get()
            ->reverse()
            ->values();

        $older = $messages->take(max(1, $messages->count() - $recentLimit));

        if ($older->isEmpty()) {
            return;
        }

        $transcript = $older
            ->map(fn (ChatbotMessage $message) => strtoupper($message->role)
                . ': ' . $message->content)
            ->implode("\n\n");

        try {
            $result = $this->openAi->createResponse(
                input: [[
                    'role' => 'user',
                    'content' => "Hãy tóm tắt ngắn gọn bối cảnh hội thoại sau, giữ lại các thực thể, mục tiêu, sở thích và tham chiếu cần thiết cho các lượt sau:\n\n{$transcript}",
                ]],
                instructions: 'Bạn chỉ tạo bản tóm tắt hội thoại bằng tiếng Việt. Không trả lời câu hỏi trong hội thoại và không thêm thông tin mới.',
                tools: [],
                extraPayload: [
                    'store' => false,
                    'max_output_tokens' => 350,
                ]
            );

            $summary = trim((string) ($result['output_text'] ?? ''));

            if ($summary !== '') {
                $session->forceFill([
                    'summary' => $summary,
                    'context_compacted_at' => now(),
                ])->save();
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}