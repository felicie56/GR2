<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotMessage extends Model
{
    use HasFactory;

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';
    public const ROLE_TOOL = 'tool';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'content',
        'sources',
        'metadata',
        'openai_response_id',
        'status',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'latency_ms',
    ];

    protected $casts = [
        'sources' => 'array',
        'metadata' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatbotSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(ChatbotFeedback::class, 'message_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ChatbotUsageLog::class, 'message_id');
    }

    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }
}