<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class OpenAiApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $requestId = null,
        public readonly array $responseBody = [],
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $statusCode ?? 0,
            $previous
        );
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();

        if (! is_array($body)) {
            $body = [];
        }

        $message = data_get(
            $body,
            'error.message',
            'OpenAI API trả về lỗi không xác định.'
        );

        $errorCode = data_get(
            $body,
            'error.code'
        ) ?: data_get(
            $body,
            'error.type'
        );

        return new self(
            message: (string) $message,
            statusCode: $response->status(),
            errorCode: $errorCode
                ? (string) $errorCode
                : null,
            requestId: $response->header('x-request-id'),
            responseBody: $body
        );
    }

    public function isRetryable(): bool
    {
        return in_array(
            $this->statusCode,
            [408, 409, 429, 500, 502, 503, 504],
            true
        );
    }

    public function isInvalidPreviousResponse(): bool
    {
        $haystack = mb_strtolower(
            implode(' ', array_filter([
                $this->errorCode,
                $this->getMessage(),
            ]))
        );

        return str_contains(
            $haystack,
            'previous_response_id'
        ) || (
            str_contains($haystack, 'response')
            && str_contains($haystack, 'not found')
        );
    }
}