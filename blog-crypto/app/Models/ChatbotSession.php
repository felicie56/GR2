<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatbotSession extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'uuid',
        'user_id',
        'guest_token_hash',
        'title',
        'summary',
        'openai_previous_response_id',
        'status',
        'last_activity_at',
        'context_compacted_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'context_compacted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ChatbotSession $session): void {
            $session->uuid ??= (string) Str::uuid();
            $session->status ??= self::STATUS_ACTIVE;
            $session->last_activity_at ??= now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'session_id')
            ->orderBy('id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ChatbotUsageLog::class, 'session_id');
    }

    public function touchActivity(): void
    {
        $this->forceFill([
            'last_activity_at' => now(),
        ])->save();
    }

    public function isOwnedBy(?User $user, ?string $guestToken): bool
    {
        if ($this->user_id !== null) {
            return $user !== null
                && (int) $this->user_id === (int) $user->id;
        }

        if (! $guestToken || ! $this->guest_token_hash) {
            return false;
        }

        return hash_equals(
            $this->guest_token_hash,
            hash('sha256', $guestToken)
        );
    }
}