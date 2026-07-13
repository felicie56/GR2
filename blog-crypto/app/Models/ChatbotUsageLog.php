<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'message_id',
        'provider',
        'model',
        'request_id',
        'input_tokens',
        'cached_input_tokens',
        'output_tokens',
        'total_tokens',
        'latency_ms',
        'tool_calls',
        'retrieved_documents',
        'status',
        'error_code',
        'error_message',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'retrieved_documents' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatbotSession::class, 'session_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatbotMessage::class, 'message_id');
    }
}