<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKnowledgeDocument extends Model
{
    use HasFactory;

    public const TYPE_BLOG = 'blog';
    public const TYPE_NEWS = 'news';

    public const STATUS_PENDING = 'pending';
    public const STATUS_INDEXING = 'indexing';
    public const STATUS_INDEXED = 'indexed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELETING = 'deleting';

    protected $fillable = [
        'source_type',
        'source_id',
        'title',
        'slug',
        'public_url',
        'content_hash',
        'openai_file_id',
        'vector_store_file_id',
        'status',
        'metadata',
        'last_error',
        'indexed_at',
        'last_synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'indexed_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function sourceModel(): BlogPost|News|null
    {
        return match ($this->source_type) {
            self::TYPE_BLOG => BlogPost::find($this->source_id),
            self::TYPE_NEWS => News::find($this->source_id),
            default => null,
        };
    }

    public function markIndexing(): void
    {
        $this->update([
            'status' => self::STATUS_INDEXING,
            'last_error' => null,
        ]);
    }

    public function markIndexed(
        string $openAiFileId,
        ?string $vectorStoreFileId = null
    ): void {
        $this->update([
            'openai_file_id' => $openAiFileId,
            'vector_store_file_id' => $vectorStoreFileId,
            'status' => self::STATUS_INDEXED,
            'indexed_at' => now(),
            'last_synced_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => mb_substr($message, 0, 65000),
            'last_synced_at' => now(),
        ]);
    }
}