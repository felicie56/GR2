<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',

        'full_name',
        'public_name',
        'headline',

        'experience_years',
        'expertise_areas',

        'experience_summary',
        'motivation',

        'sample_article_title',
        'sample_article_content',

        'website_url',
        'linkedin_url',
        'x_url',

        'truthful_information_confirmed',
        'content_policy_confirmed',

        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'user_seen_at',
    ];

    protected $casts = [
        'expertise_areas' => 'array',
        'truthful_information_confirmed' => 'boolean',
        'content_policy_confirmed' => 'boolean',
        'reviewed_at' => 'datetime',
        'user_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
            default => strtoupper((string) $this->status),
        };
    }
}