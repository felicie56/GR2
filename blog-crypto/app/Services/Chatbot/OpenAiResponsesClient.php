<?php

namespace App\Services\Chatbot;

use App\Exceptions\OpenAiApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenAiResponsesClient
{
    /**
     * @param  string|array<int, array<string, mixed>>  $input
     * @param  array<int, array<string, mixed>>  $tools
     * @param  array<string, mixed>  $extraPayload
     * @return array<string, mixed>
     */
    public function createResponse(
        string|array $input,
        ?string $previousResponseId = null,
        ?string $instructions = null,
        array $tools = [],
        array $extraPayload = []
    ): array {
        $this->ensureConfigured();

        $payload = [
            'model' => (string) config('chatbot.openai.model'),
            'instructions' => $instructions
                ?? (string) config('chatbot.system_instructions'),
            'input' => $input,
            'store' => (bool) config(
                'chatbot.openai.store_responses',
                true
            ),
            'max_output_tokens' => (int) config(
                'chatbot.openai.max_output_tokens',
                700
            ),
        ];

        $reasoningEffort = trim((string) config(
            'chatbot.openai.reasoning_effort',
            'low'
        ));

        if ($reasoningEffort !== '') {
            $payload['reasoning'] = [
                'effort' => $reasoningEffort,
            ];
        }

        if ($previousResponseId) {
            $payload['previous_response_id'] = $previousResponseId;
        }

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        foreach ($extraPayload as $key => $value) {
            $payload[$key] = $value;
        }

        $response = $this->postWithRetry('/responses', $payload);
        $body = $response->json();

        if (! is_array($body)) {
            throw new OpenAiApiException(
                message: 'OpenAI API trả về dữ liệu không phải JSON hợp lệ.',
                statusCode: $response->status(),
                requestId: $response->header('x-request-id')
            );
        }

        $status = (string) ($body['status'] ?? '');

        if ($status === 'failed' || data_get($body, 'error.message')) {
            throw new OpenAiApiException(
                message: (string) data_get(
                    $body,
                    'error.message',
                    'OpenAI không thể hoàn thành phản hồi.'
                ),
                statusCode: $response->status(),
                errorCode: data_get($body, 'error.code'),
                requestId: $response->header('x-request-id'),
                responseBody: $body
            );
        }

        $functionCalls = $this->extractFunctionCalls($body);
        $outputText = $this->extractOutputText($body);

        if ($outputText === '' && $functionCalls === []) {
            $reason = data_get($body, 'incomplete_details.reason');

            throw new OpenAiApiException(
                message: $reason
                    ? 'OpenAI chưa tạo được nội dung trả lời. Lý do: ' . $reason
                    : 'OpenAI không trả về nội dung văn bản hoặc lời gọi công cụ.',
                statusCode: $response->status(),
                errorCode: $reason ? (string) $reason : null,
                requestId: $response->header('x-request-id'),
                responseBody: $body
            );
        }

        return [
            'id' => isset($body['id']) ? (string) $body['id'] : null,
            'status' => $status,
            'model' => isset($body['model'])
                ? (string) $body['model']
                : (string) config('chatbot.openai.model'),
            'output_text' => $outputText,
            'annotations' => $this->extractAnnotations($body),
            'file_search_results' => $this->extractFileSearchResults($body),
            'function_calls' => $functionCalls,
            'output' => is_array($body['output'] ?? null)
                ? $body['output']
                : [],
            'usage' => [
                'input_tokens' => $this->nullableInt(
                    data_get($body, 'usage.input_tokens')
                ),
                'cached_input_tokens' => $this->nullableInt(
                    data_get(
                        $body,
                        'usage.input_tokens_details.cached_tokens'
                    )
                ),
                'output_tokens' => $this->nullableInt(
                    data_get($body, 'usage.output_tokens')
                ),
                'total_tokens' => $this->nullableInt(
                    data_get($body, 'usage.total_tokens')
                ),
            ],
            'request_id' => $response->header('x-request-id'),
            'raw' => $body,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postWithRetry(string $endpoint, array $payload): Response
    {
        $maxRetries = max(0, (int) config(
            'chatbot.openai.retry_times',
            2
        ));

        $delayMs = max(0, (int) config(
            'chatbot.openai.retry_delay_ms',
            800
        ));

        $attempt = 0;

        while (true) {
            try {
                $response = $this->request()
                    ->asJson()
                    ->post($this->url($endpoint), $payload);

                if ($response->successful()) {
                    return $response;
                }

                $exception = OpenAiApiException::fromResponse($response);

                if ($attempt >= $maxRetries || ! $exception->isRetryable()) {
                    throw $exception;
                }
            } catch (ConnectionException $exception) {
                if ($attempt >= $maxRetries) {
                    throw new OpenAiApiException(
                        message: 'Không thể kết nối tới OpenAI API: '
                            . $exception->getMessage(),
                        previous: $exception
                    );
                }
            } catch (OpenAiApiException $exception) {
                if ($attempt >= $maxRetries || ! $exception->isRetryable()) {
                    throw $exception;
                }
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    'Lỗi bất ngờ khi gọi OpenAI API: '
                        . $exception->getMessage(),
                    previous: $exception
                );
            }

            $attempt++;

            if ($delayMs > 0) {
                usleep(($delayMs * $attempt) * 1000);
            }
        }
    }

    private function request(): PendingRequest
    {
        $request = Http::withToken(
            (string) config('chatbot.openai.api_key')
        )
            ->acceptJson()
            ->connectTimeout((int) config(
                'chatbot.openai.connect_timeout_seconds',
                10
            ))
            ->timeout((int) config(
                'chatbot.openai.timeout_seconds',
                90
            ))
            ->withHeaders([
                'User-Agent' => 'CryptoBlog-Chatbot/2.0',
            ]);

        $organizationId = trim((string) config(
            'chatbot.openai.organization_id'
        ));

        if ($organizationId !== '') {
            $request = $request->withHeaders([
                'OpenAI-Organization' => $organizationId,
            ]);
        }

        $projectId = trim((string) config(
            'chatbot.openai.project_id'
        ));

        if ($projectId !== '') {
            $request = $request->withHeaders([
                'OpenAI-Project' => $projectId,
            ]);
        }

        return $request;
    }

    private function url(string $endpoint): string
    {
        return rtrim(
            (string) config(
                'chatbot.openai.base_url',
                'https://api.openai.com/v1'
            ),
            '/'
        ) . '/' . ltrim($endpoint, '/');
    }

    private function ensureConfigured(): void
    {
        if (! config('chatbot.enabled')) {
            throw new RuntimeException('Chatbot AI đang bị tắt trong cấu hình.');
        }

        if (trim((string) config('chatbot.openai.api_key')) === '') {
            throw new RuntimeException(
                'OPENAI_API_KEY chưa được cấu hình trong file .env.'
            );
        }

        if (trim((string) config('chatbot.openai.model')) === '') {
            throw new RuntimeException('OPENAI_CHAT_MODEL chưa được cấu hình.');
        }
    }

    /** @param array<string, mixed> $body */
    private function extractOutputText(array $body): string
    {
        $topLevel = $body['output_text'] ?? null;

        if (is_string($topLevel)) {
            return trim($topLevel);
        }

        $parts = [];

        foreach ((array) ($body['output'] ?? []) as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'message') {
                continue;
            }

            foreach ((array) ($item['content'] ?? []) as $content) {
                if (
                    is_array($content)
                    && ($content['type'] ?? null) === 'output_text'
                    && is_string($content['text'] ?? null)
                ) {
                    $parts[] = $content['text'];
                }
            }
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<int, array<string, mixed>>
     */
    private function extractAnnotations(array $body): array
    {
        $annotations = [];

        foreach ((array) ($body['output'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ((array) ($item['content'] ?? []) as $content) {
                if (! is_array($content)) {
                    continue;
                }

                foreach ((array) ($content['annotations'] ?? []) as $annotation) {
                    if (is_array($annotation)) {
                        $annotations[] = $annotation;
                    }
                }
            }
        }

        return $annotations;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<int, array<string, mixed>>
     */
    private function extractFunctionCalls(array $body): array
    {
        $calls = [];

        foreach ((array) ($body['output'] ?? []) as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'function_call') {
                continue;
            }

            $arguments = [];
            $rawArguments = $item['arguments'] ?? '{}';

            if (is_string($rawArguments)) {
                $decoded = json_decode($rawArguments, true);
                $arguments = is_array($decoded) ? $decoded : [];
            }

            $calls[] = [
                'id' => $item['id'] ?? null,
                'call_id' => $item['call_id'] ?? null,
                'name' => $item['name'] ?? null,
                'arguments' => $arguments,
                'raw_arguments' => $rawArguments,
            ];
        }

        return $calls;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<int, array<string, mixed>>
     */
    private function extractFileSearchResults(array $body): array
    {
        $results = [];

        foreach ((array) ($body['output'] ?? []) as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'file_search_call') {
                continue;
            }

            foreach ((array) ($item['results'] ?? []) as $result) {
                if (is_array($result)) {
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}