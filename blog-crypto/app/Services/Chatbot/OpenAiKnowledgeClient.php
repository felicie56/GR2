<?php

namespace App\Services\Chatbot;

use App\Exceptions\OpenAiApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiKnowledgeClient
{
    /**
     * Tạo Vector Store mới.
     *
     * @return array<string, mixed>
     */
    public function createVectorStore(
        string $name
    ): array {
        return $this->json(
            $this->jsonRequest()->post(
                $this->url('/vector_stores'),
                [
                    'name' => $name,
                ]
            )
        );
    }

    /**
     * Lấy thông tin Vector Store.
     *
     * @return array<string, mixed>
     */
    public function retrieveVectorStore(
        string $vectorStoreId
    ): array {
        return $this->json(
            $this->baseRequest()->get(
                $this->url(
                    '/vector_stores/'
                    . urlencode($vectorStoreId)
                )
            )
        );
    }

    /**
     * Upload file text/Markdown lên OpenAI Files.
     *
     * @return array<string, mixed>
     */
    public function uploadTextFile(
        string $filename,
        string $contents
    ): array {
        $response = $this->baseRequest()
            ->attach(
                'file',
                $contents,
                $filename
            )
            ->post(
                $this->url('/files'),
                [
                    'purpose' => 'assistants',
                ]
            );

        return $this->json(
            $response
        );
    }

    /**
     * Gắn file đã upload vào Vector Store.
     *
     * @param array<string, string|int|float|bool> $attributes
     * @return array<string, mixed>
     */
    public function attachFile(
        string $vectorStoreId,
        string $fileId,
        array $attributes = []
    ): array {
        $payload = [
            'file_id' => $fileId,
        ];

        if ($attributes !== []) {
            $payload['attributes'] =
                $attributes;
        }

        return $this->json(
            $this->jsonRequest()->post(
                $this->url(
                    '/vector_stores/'
                    . urlencode($vectorStoreId)
                    . '/files'
                ),
                $payload
            )
        );
    }

    /**
     * Kiểm tra trạng thái một file
     * trong Vector Store.
     *
     * @return array<string, mixed>
     */
    public function retrieveVectorStoreFile(
        string $vectorStoreId,
        string $fileId
    ): array {
        return $this->json(
            $this->baseRequest()->get(
                $this->url(
                    '/vector_stores/'
                    . urlencode($vectorStoreId)
                    . '/files/'
                    . urlencode($fileId)
                )
            )
        );
    }

    /**
     * Gỡ file khỏi Vector Store.
     */
    public function deleteVectorStoreFile(
        string $vectorStoreId,
        string $fileId
    ): void {
        $response = $this->baseRequest()
            ->delete(
                $this->url(
                    '/vector_stores/'
                    . urlencode($vectorStoreId)
                    . '/files/'
                    . urlencode($fileId)
                )
            );

        $this->assertSuccessful(
            $response
        );
    }

    /**
     * Xóa file khỏi OpenAI Files.
     */
    public function deleteFile(
        string $fileId
    ): void {
        $response = $this->baseRequest()
            ->delete(
                $this->url(
                    '/files/'
                    . urlencode($fileId)
                )
            );

        $this->assertSuccessful(
            $response
        );
    }

    private function jsonRequest(): PendingRequest
    {
        return $this->baseRequest()
            ->asJson();
    }

    private function baseRequest(): PendingRequest
    {
        $apiKey = trim(
            (string) config(
                'chatbot.openai.api_key'
            )
        );

        if ($apiKey === '') {
            throw new RuntimeException(
                'OPENAI_API_KEY chưa được cấu hình '
                . 'trong file .env.'
            );
        }

        $request = Http::withToken(
            $apiKey
        )
            ->acceptJson()
            ->connectTimeout(
                (int) config(
                    'chatbot.openai.connect_timeout_seconds',
                    10
                )
            )
            ->timeout(
                (int) config(
                    'chatbot.openai.timeout_seconds',
                    90
                )
            )
            ->withHeaders([
                'User-Agent' =>
                    'CryptoBlog-KnowledgeIndexer/1.0',
            ]);

        $organizationId = trim(
            (string) config(
                'chatbot.openai.organization_id'
            )
        );

        if ($organizationId !== '') {
            $request = $request->withHeaders([
                'OpenAI-Organization' =>
                    $organizationId,
            ]);
        }

        $projectId = trim(
            (string) config(
                'chatbot.openai.project_id'
            )
        );

        if ($projectId !== '') {
            $request = $request->withHeaders([
                'OpenAI-Project' =>
                    $projectId,
            ]);
        }

        return $request;
    }

    private function url(
        string $endpoint
    ): string {
        return rtrim(
            (string) config(
                'chatbot.openai.base_url',
                'https://api.openai.com/v1'
            ),
            '/'
        )
            . '/'
            . ltrim($endpoint, '/');
    }

    /**
     * @return array<string, mixed>
     */
    private function json(
        Response $response
    ): array {
        $this->assertSuccessful(
            $response
        );

        $body = $response->json();

        if (! is_array($body)) {
            throw new RuntimeException(
                'OpenAI API trả về dữ liệu '
                . 'không phải JSON hợp lệ.'
            );
        }

        return $body;
    }

    private function assertSuccessful(
        Response $response
    ): void {
        if (! $response->successful()) {
            throw OpenAiApiException::fromResponse(
                $response
            );
        }
    }
}