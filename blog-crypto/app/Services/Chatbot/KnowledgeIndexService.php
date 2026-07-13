<?php

namespace App\Services\Chatbot;

use App\Models\AiKnowledgeDocument;
use App\Models\BlogPost;
use App\Models\News;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class KnowledgeIndexService
{
    public function __construct(
        private readonly OpenAiKnowledgeClient $openAi,
        private readonly KnowledgeDocumentBuilder $builder
    ) {
    }

    public function vectorStoreId(): ?string
    {
        $configured = trim((string) config(
            'chatbot.openai.vector_store_id'
        ));

        if ($configured !== '') {
            return $configured;
        }

        $path = 'chatbot/vector_store_id.txt';

        if (Storage::disk('local')->exists($path)) {
            $stored = trim(
                (string) Storage::disk('local')->get($path)
            );

            return $stored !== ''
                ? $stored
                : null;
        }

        return null;
    }

    public function ensureVectorStore(
        bool $forceCreate = false
    ): string {
        $existing = $this->vectorStoreId();

        if ($existing && ! $forceCreate) {
            $this->openAi->retrieveVectorStore(
                $existing
            );

            return $existing;
        }

        $created = $this->openAi->createVectorStore(
            (string) config(
                'chatbot.openai.vector_store_name',
                'CryptoBlog Knowledge Base'
            )
        );

        $id = trim(
            (string) ($created['id'] ?? '')
        );

        if ($id === '') {
            throw new RuntimeException(
                'OpenAI không trả về ID của Vector Store vừa tạo.'
            );
        }

        Storage::disk('local')->put(
            'chatbot/vector_store_id.txt',
            $id
        );

        return $id;
    }

    public function syncBlog(
        int|BlogPost $blog
    ): ?AiKnowledgeDocument {
        $model = $blog instanceof BlogPost
            ? $blog
            : BlogPost::find($blog);

        if (! $model) {
            $this->deleteSource(
                AiKnowledgeDocument::TYPE_BLOG,
                (int) $blog
            );

            return null;
        }

        return $this->sync($model);
    }

    public function syncNews(
        int|News $news
    ): ?AiKnowledgeDocument {
        $model = $news instanceof News
            ? $news
            : News::find($news);

        if (! $model) {
            $this->deleteSource(
                AiKnowledgeDocument::TYPE_NEWS,
                (int) $news
            );

            return null;
        }

        return $this->sync($model);
    }

    public function sync(
        Model $source,
        bool $force = false
    ): ?AiKnowledgeDocument {
        if (! $this->builder->isEligible($source)) {
            $type = $source instanceof BlogPost
                ? AiKnowledgeDocument::TYPE_BLOG
                : AiKnowledgeDocument::TYPE_NEWS;

            $this->deleteSource(
                $type,
                (int) $source->getKey()
            );

            return null;
        }

        $vectorStoreId =
            $this->ensureVectorStore();

        $payload =
            $this->builder->build($source);

        /*
         * Không dùng updateOrCreate ở đây.
         * Phải kiểm tra hash cũ trước khi cập nhật,
         * nếu không tài liệu nào cũng bị upload lại.
         */
        $document = AiKnowledgeDocument::query()
            ->firstOrNew([
                'source_type' =>
                    $payload['source_type'],

                'source_id' =>
                    $payload['source_id'],
            ]);

        $contentUnchanged =
            $document->exists
            && $document->status
                === AiKnowledgeDocument::STATUS_INDEXED
            && is_string($document->content_hash)
            && hash_equals(
                (string) $document->content_hash,
                (string) $payload['content_hash']
            )
            && is_string($document->openai_file_id)
            && trim($document->openai_file_id) !== '';

        if (! $force && $contentUnchanged) {
            $document->forceFill([
                'title' =>
                    $payload['title'],

                'slug' =>
                    $payload['slug'],

                'public_url' =>
                    $payload['public_url'],

                'metadata' =>
                    $payload['metadata'],

                'last_synced_at' =>
                    now(),

                'last_error' =>
                    null,
            ])->save();

            return $document->fresh();
        }

        $document->fill([
            'title' =>
                $payload['title'],

            'slug' =>
                $payload['slug'],

            'public_url' =>
                $payload['public_url'],

            'content_hash' =>
                $payload['content_hash'],

            'metadata' =>
                $payload['metadata'],

            'status' =>
                AiKnowledgeDocument::STATUS_PENDING,

            'last_error' =>
                null,
        ]);

        $document->save();

        $oldOpenAiFileId =
            $document->openai_file_id;

        $oldVectorStoreFileId =
            $document->vector_store_file_id;

        $newFileId = null;

        $document->markIndexing();

        try {
            $uploaded =
                $this->openAi->uploadTextFile(
                    $payload['filename'],
                    $payload['content']
                );

            $newFileId = trim(
                (string) ($uploaded['id'] ?? '')
            );

            if ($newFileId === '') {
                throw new RuntimeException(
                    'OpenAI không trả về file_id sau khi upload.'
                );
            }

            $attached =
                $this->openAi->attachFile(
                    $vectorStoreId,
                    $newFileId,
                    $payload['vector_attributes']
                );

            $vectorStoreFileId = trim(
                (string) (
                    $attached['id']
                    ?? $newFileId
                )
            );

            $this->waitUntilIndexed(
                $vectorStoreId,
                $vectorStoreFileId
                    ?: $newFileId
            );

            $document->forceFill([
                'title' =>
                    $payload['title'],

                'slug' =>
                    $payload['slug'],

                'public_url' =>
                    $payload['public_url'],

                'content_hash' =>
                    $payload['content_hash'],

                'metadata' =>
                    $payload['metadata'],
            ])->save();

            $document->markIndexed(
                $newFileId,
                $vectorStoreFileId
                    ?: $newFileId
            );

            $this->deleteRemoteFileQuietly(
                $vectorStoreId,
                $oldVectorStoreFileId,
                $oldOpenAiFileId,
                exceptFileId: $newFileId
            );

            return $document->fresh();
        } catch (Throwable $exception) {
            if ($newFileId) {
                try {
                    $this->openAi->deleteFile(
                        $newFileId
                    );
                } catch (Throwable) {
                    // Không che lỗi đồng bộ gốc.
                }
            }

            $document->markFailed(
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    public function deleteSource(
        string $sourceType,
        int $sourceId
    ): void {
        $document = AiKnowledgeDocument::query()
            ->where(
                'source_type',
                $sourceType
            )
            ->where(
                'source_id',
                $sourceId
            )
            ->first();

        if (! $document) {
            return;
        }

        $document->update([
            'status' =>
                AiKnowledgeDocument::STATUS_DELETING,
        ]);

        $vectorStoreId =
            $this->vectorStoreId();

        if ($vectorStoreId) {
            $this->deleteRemoteFileQuietly(
                $vectorStoreId,
                $document->vector_store_file_id,
                $document->openai_file_id
            );
        }

        $document->delete();
    }

    private function waitUntilIndexed(
        string $vectorStoreId,
        string $fileId
    ): void {
        $attempts = max(
            1,
            (int) config(
                'chatbot.retrieval.poll_attempts',
                30
            )
        );

        $delayMs = max(
            100,
            (int) config(
                'chatbot.retrieval.poll_delay_ms',
                1000
            )
        );

        for (
            $attempt = 1;
            $attempt <= $attempts;
            $attempt++
        ) {
            $file =
                $this->openAi
                    ->retrieveVectorStoreFile(
                        $vectorStoreId,
                        $fileId
                    );

            $status = strtolower(
                (string) ($file['status'] ?? '')
            );

            if ($status === 'completed') {
                return;
            }

            if (
                in_array(
                    $status,
                    ['failed', 'cancelled'],
                    true
                )
            ) {
                $error = data_get(
                    $file,
                    'last_error.message',
                    'OpenAI không thể lập chỉ mục tài liệu.'
                );

                throw new RuntimeException(
                    (string) $error
                );
            }

            usleep(
                $delayMs * 1000
            );
        }

        throw new RuntimeException(
            'Quá thời gian chờ OpenAI lập chỉ mục tài liệu.'
        );
    }

    private function deleteRemoteFileQuietly(
        string $vectorStoreId,
        ?string $vectorStoreFileId,
        ?string $openAiFileId,
        ?string $exceptFileId = null
    ): void {
        if (
            $vectorStoreFileId
            && $vectorStoreFileId
                !== $exceptFileId
        ) {
            try {
                $this->openAi
                    ->deleteVectorStoreFile(
                        $vectorStoreId,
                        $vectorStoreFileId
                    );
            } catch (Throwable) {
                // File có thể đã bị xóa.
            }
        }

        if (
            $openAiFileId
            && $openAiFileId
                !== $exceptFileId
        ) {
            try {
                $this->openAi->deleteFile(
                    $openAiFileId
                );
            } catch (Throwable) {
                // Không làm hỏng luồng đồng bộ.
            }
        }
    }
}