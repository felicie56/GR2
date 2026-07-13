<?php

namespace App\Services\Chatbot;

use JsonException;
use Throwable;

class ChatbotToolService
{
    public function __construct(
        private readonly KnowledgeIndexService $knowledge,
        private readonly CryptoToolService $crypto
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function definitions(): array
    {
        $tools = [];

        if ((bool) config('chatbot.agent.enable_file_search', true)) {
            $vectorStoreId = $this->knowledge->vectorStoreId();

            if ($vectorStoreId) {
                $tools[] = [
                    'type' => 'file_search',
                    'vector_store_ids' => [$vectorStoreId],
                    'max_num_results' => max(1, (int) config(
                        'chatbot.retrieval.max_results',
                        4
                    )),
                ];
            }
        }

        if ((bool) config('chatbot.agent.enable_crypto_tools', true)) {
            $tools[] = [
                'type' => 'function',
                'name' => 'get_crypto_quote',
                'description' => 'Lấy giá mới nhất, biến động 24 giờ và thời điểm cập nhật của một đồng coin từ database CryptoBlog.',
                'strict' => true,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'coin' => [
                            'type' => 'string',
                            'description' => 'Tên hoặc mã coin, ví dụ Bitcoin, BTC, Ethereum hoặc ETH.',
                        ],
                    ],
                    'required' => ['coin'],
                    'additionalProperties' => false,
                ],
            ];

            $tools[] = [
                'type' => 'function',
                'name' => 'get_crypto_history',
                'description' => 'Tóm tắt biến động giá của một đồng coin trong số giờ gần đây từ database CryptoBlog.',
                'strict' => true,
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'coin' => [
                            'type' => 'string',
                            'description' => 'Tên hoặc mã coin.',
                        ],
                        'hours' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 168,
                            'description' => 'Số giờ cần thống kê, tối đa 168 giờ.',
                        ],
                    ],
                    'required' => ['coin', 'hours'],
                    'additionalProperties' => false,
                ],
            ];
        }

        return $tools;
    }

    /**
     * @param  array<int, array<string, mixed>>  $calls
     * @return array{
     *     outputs: array<int, array<string, mixed>>,
     *     logs: array<int, array<string, mixed>>,
     *     source_hints: array<int, array<string, mixed>>
     * }
     */
    public function execute(array $calls): array
    {
        $outputs = [];
        $logs = [];
        $sourceHints = [];

        foreach ($calls as $call) {
            $name = (string) ($call['name'] ?? '');
            $callId = (string) ($call['call_id'] ?? '');
            $arguments = is_array($call['arguments'] ?? null)
                ? $call['arguments']
                : [];

            try {
                $result = match ($name) {
                    'get_crypto_quote' => $this->crypto->quote(
                        (string) ($arguments['coin'] ?? '')
                    ),
                    'get_crypto_history' => $this->crypto->history(
                        (string) ($arguments['coin'] ?? ''),
                        (int) ($arguments['hours'] ?? 24)
                    ),
                    default => [
                        'success' => false,
                        'message' => 'Công cụ không được hệ thống hỗ trợ.',
                    ],
                };
            } catch (Throwable $exception) {
                $result = [
                    'success' => false,
                    'message' => 'Công cụ gặp lỗi khi truy vấn dữ liệu.',
                    'error' => $exception->getMessage(),
                ];
            }

            $encoded = $this->encode($result);

            $outputs[] = [
                'type' => 'function_call_output',
                'call_id' => $callId,
                'output' => $encoded,
            ];

            $logs[] = [
                'name' => $name,
                'call_id' => $callId,
                'arguments' => $arguments,
                'result' => $result,
            ];

            if (
                ($result['success'] ?? false)
                && is_string($result['public_url'] ?? null)
            ) {
                $sourceHints[] = [
                    'type' => 'crypto',
                    'title' => isset($result['coin']['name'])
                        ? $result['coin']['name'] . ' ('
                            . ($result['coin']['symbol'] ?? '') . ')'
                        : 'Dữ liệu crypto',
                    'url' => $result['public_url'],
                    'excerpt' => 'Dữ liệu giá được lấy từ database của website.',
                ];
            }
        }

        return compact('outputs', 'logs', 'sourceHints');
    }

    /** @param array<string, mixed> $value */
    private function encode(array $value): string
    {
        try {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return '{"success":false,"message":"Không thể mã hóa kết quả công cụ."}';
        }
    }
}