<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFeedback extends Model
{
    use HasFactory;

    protected $table = 'chatbot_feedback';

    public const RATING_HELPFUL = 'helpful';
    public const RATING_NOT_HELPFUL = 'not_helpful';

    protected $fillable = [
        'message_id',
        'user_id',
        'guest_token_hash',
        'rating',
        'reason',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatbotMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}