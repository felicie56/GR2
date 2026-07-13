<?php

namespace App\Services\Chatbot;

use App\Exceptions\OpenAiApiException;
use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use App\Models\ChatbotUsageLog;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ChatbotTurnService
{
    public function __construct(
        private readonly OpenAiResponsesClient $openAi,
        private readonly ConversationSessionService $sessions,
        private readonly ChatbotToolService $tools,
        private readonly CitationMapper $citations,
        private readonly ConversationMemoryService $memory
    ) {
    }

    /** @return array<string, mixed> */
    public function ask(
        ChatbotSession $session,
        string $message,
        ?User $user = null
    ): array {
        $message = $this->validateMessage($message);

        $userMessage = ChatbotMessage::create([
            'session_id' => $session->id,
            'user_id' => $user?->id,
            'role' => ChatbotMessage::ROLE_USER,
            'content' => $message,
            'status' => ChatbotMessage::STATUS_COMPLETED,
        ]);

        $assistantMessage = ChatbotMessage::create([
            'session_id' => $session->id,
            'role' => ChatbotMessage::ROLE_ASSISTANT,
            'content' => '',
            'sources' => [],
            'metadata' => [],
            'status' => ChatbotMessage::STATUS_PENDING,
        ]);

        $startedAt = hrtime(true);
        $previousResponseId = $this->shouldUsePreviousResponseId()
            ? $session->openai_previous_response_id
            : null;

        try {
            $result = $this->runAgent(
                session: $session,
                currentMessage: $message,
                previousResponseId: $previousResponseId
            );
        } catch (OpenAiApiException $exception) {
            if (
                $previousResponseId
                && $exception->isInvalidPreviousResponse()
                && (bool) config(
                    'chatbot.openai.fallback_to_local_history',
                    true
                )
            ) {
                $session->forceFill([
                    'openai_previous_response_id' => null,
                ])->save();

                try {
                    $result = $this->runAgent(
                        session: $session,
                        currentMessage: $message,
                        previousResponseId: null
                    );
                } catch (Throwable $fallbackException) {
                    return $this->handleFailure(
                        $session,
                        $assistantMessage,
                        $startedAt,
                        $fallbackException
                    );
                }
            } else {
                return $this->handleFailure(
                    $session,
                    $assistantMessage,
                    $startedAt,
                    $exception
                );
            }
        } catch (Throwable $exception) {
            return $this->handleFailure(
                $session,
                $assistantMessage,
                $startedAt,
                $exception
            );
        }

        $latencyMs = $this->latencyMs($startedAt);
        $usage = $result['usage'];
        $sources = $this->citations->map(
            $result['annotations'],
            $result['file_search_results'],
            $result['source_hints']
        );

        $assistantMessage->update([
            'content' => $result['output_text'],
            'sources' => $sources,
            'metadata' => [
                'provider' => 'openai',
                'model' => $result['model'],
                'request_id' => $result['request_id'],
                'annotations' => $result['annotations'],
                'tool_calls' => $result['tool_logs'],
                'file_search_results_count' => count(
                    $result['file_search_results']
                ),
            ],
            'openai_response_id' => $result['id'],
            'status' => ChatbotMessage::STATUS_COMPLETED,
            'input_tokens' => $usage['input_tokens'],
            'output_tokens' => $usage['output_tokens'],
            'total_tokens' => $usage['total_tokens'],
            'latency_ms' => $latencyMs,
        ]);

        $session->forceFill([
            'title' => $session->title
                ?: $this->sessions->makeTitle($message),
            'openai_previous_response_id' => $this->shouldUsePreviousResponseId()
                ? $result['id']
                : null,
            'last_activity_at' => now(),
        ])->save();

        ChatbotUsageLog::create([
            'session_id' => $session->id,
            'message_id' => $assistantMessage->id,
            'provider' => 'openai',
            'model' => $result['model'],
            'request_id' => $result['request_id'],
            'input_tokens' => $usage['input_tokens'],
            'cached_input_tokens' => $usage['cached_input_tokens'],
            'output_tokens' => $usage['output_tokens'],
            'total_tokens' => $usage['total_tokens'],
            'latency_ms' => $latencyMs,
            'tool_calls' => $result['tool_logs'],
            'retrieved_documents' => $sources,
            'status' => 'success',
        ]);

        $this->memory->compactIfNeeded($session->fresh());

        return [
            'session' => $session->fresh(),
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage->fresh(),
            'answer' => $result['output_text'],
            'sources' => $sources,
            'suggestions' => $this->suggestions($result['tool_logs'], $sources),
            'response_id' => $result['id'],
            'model' => $result['model'],
            'usage' => $usage,
            'latency_ms' => $latencyMs,
        ];
    }

    /** @return array<string, mixed> */
    private function runAgent(
        ChatbotSession $session,
        string $currentMessage,
        ?string $previousResponseId
    ): array {
        $definitions = $this->tools->definitions();
        $hasFileSearch = collect($definitions)
            ->contains(fn ($tool) => ($tool['type'] ?? null) === 'file_search');

        $input = $previousResponseId
            ? [[
                'role' => 'user',
                'content' => $currentMessage,
            ]]
            : $this->sessions->buildLocalContext($session);

        $currentPreviousResponseId = $previousResponseId;
        $maxRounds = max(1, (int) config(
            'chatbot.agent.max_tool_rounds',
            3
        ));

        $usage = [
            'input_tokens' => 0,
            'cached_input_tokens' => 0,
            'output_tokens' => 0,
            'total_tokens' => 0,
        ];

        $annotations = [];
        $fileSearchResults = [];
        $toolLogs = [];
        $sourceHints = [];
        $lastResult = null;

        for ($round = 1; $round <= $maxRounds; $round++) {
            $extraPayload = [];

            if ($hasFileSearch) {
                $extraPayload['include'] = ['file_search_call.results'];
            }

            $result = $this->openAi->createResponse(
                input: $input,
                previousResponseId: $currentPreviousResponseId,
                instructions: (string) config('chatbot.system_instructions'),
                tools: $definitions,
                extraPayload: $extraPayload
            );

            $lastResult = $result;
            $usage = $this->mergeUsage($usage, $result['usage'] ?? []);
            $annotations = array_merge(
                $annotations,
                $result['annotations'] ?? []
            );
            $fileSearchResults = array_merge(
                $fileSearchResults,
                $result['file_search_results'] ?? []
            );

            $functionCalls = $result['function_calls'] ?? [];

            if ($functionCalls === []) {
                $answer = trim((string) ($result['output_text'] ?? ''));

                if ($answer === '') {
                    throw new RuntimeException(
                        'Mô hình kết thúc nhưng không tạo câu trả lời.'
                    );
                }

                return [
                    'id' => $result['id'],
                    'model' => $result['model'],
                    'request_id' => $result['request_id'],
                    'output_text' => $answer,
                    'usage' => $usage,
                    'annotations' => $annotations,
                    'file_search_results' => $fileSearchResults,
                    'tool_logs' => $toolLogs,
                    'source_hints' => $sourceHints,
                ];
            }

            $executed = $this->tools->execute($functionCalls);
            $toolLogs = array_merge($toolLogs, $executed['logs']);
            $sourceHints = array_merge(
                $sourceHints,
                $executed['sourceHints']
            );

            $input = $executed['outputs'];
            $currentPreviousResponseId = $result['id'];
        }

        throw new RuntimeException(
            'Chatbot đã vượt quá số vòng gọi công cụ cho phép.'
        );
    }

    /**
     * @param  array<string, int|null>  $total
     * @param  array<string, int|null>  $current
     * @return array<string, int>
     */
    private function mergeUsage(array $total, array $current): array
    {
        foreach (array_keys($total) as $key) {
            $total[$key] = (int) ($total[$key] ?? 0)
                + (int) ($current[$key] ?? 0);
        }

        return $total;
    }

    /** @return never */
    private function handleFailure(
        ChatbotSession $session,
        ChatbotMessage $assistantMessage,
        int $startedAt,
        Throwable $exception
    ): never {
        $latencyMs = $this->latencyMs($startedAt);
        $errorCode = $exception instanceof OpenAiApiException
            ? $exception->errorCode
            : null;
        $requestId = $exception instanceof OpenAiApiException
            ? $exception->requestId
            : null;

        $assistantMessage->update([
            'content' => 'Xin lỗi, hệ thống AI hiện chưa thể trả lời. Vui lòng thử lại sau.',
            'metadata' => [
                'provider' => 'openai',
                'request_id' => $requestId,
                'error_code' => $errorCode,
            ],
            'status' => ChatbotMessage::STATUS_FAILED,
            'latency_ms' => $latencyMs,
        ]);

        ChatbotUsageLog::create([
            'session_id' => $session->id,
            'message_id' => $assistantMessage->id,
            'provider' => 'openai',
            'model' => (string) config('chatbot.openai.model'),
            'request_id' => $requestId,
            'latency_ms' => $latencyMs,
            'tool_calls' => [],
            'retrieved_documents' => [],
            'status' => 'error',
            'error_code' => $errorCode,
            'error_message' => $exception->getMessage(),
        ]);

        $session->touchActivity();

        throw $exception;
    }

    private function validateMessage(string $message): string
    {
        $message = trim($message);

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => 'Bạn chưa nhập câu hỏi.',
            ]);
        }

        $maxCharacters = (int) config(
            'chatbot.conversation.max_message_characters',
            2000
        );

        if (mb_strlen($message) > $maxCharacters) {
            throw ValidationException::withMessages([
                'message' => "Câu hỏi chỉ được tối đa {$maxCharacters} ký tự.",
            ]);
        }

        return $message;
    }

    private function shouldUsePreviousResponseId(): bool
    {
        return (bool) config('chatbot.openai.store_responses', true)
            && (bool) config(
                'chatbot.openai.use_previous_response_id',
                true
            );
    }

    /**
     * @param  array<int, array<string, mixed>>  $toolLogs
     * @param  array<int, array<string, mixed>>  $sources
     * @return array<int, string>
     */
    private function suggestions(array $toolLogs, array $sources): array
    {
        $usedCrypto = collect($toolLogs)->contains(
            fn ($log) => str_starts_with(
                (string) ($log['name'] ?? ''),
                'get_crypto_'
            )
        );

        if ($usedCrypto) {
            return [
                'Biến động 24 giờ của coin này như thế nào?',
                'Website có bài nào liên quan đến coin này?',
                'Rủi ro khi đầu tư crypto là gì?',
            ];
        }

        if ($sources !== []) {
            return [
                'Tóm tắt các nguồn này giúp tôi',
                'Có tin mới hơn về chủ đề này không?',
                'Chủ đề này ảnh hưởng thị trường thế nào?',
            ];
        }

        return [
            'Tin tức crypto mới trên website có gì?',
            'Giá Bitcoin hiện tại là bao nhiêu?',
            'Làm sao để trở thành tác giả?',
        ];
    }

    private function latencyMs(int $startedAt): int
    {
        return (int) round((hrtime(true) - $startedAt) / 1_000_000);
    }
}