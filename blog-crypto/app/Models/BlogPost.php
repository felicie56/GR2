<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'thumbnail',
        'category_id',
        'user_id',
        'author_id',
        'status',
        'review_note',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
        'review_seen_at',
        'published_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'review_seen_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlogPostImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'blog_post_id')->latest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'blog_post_id');
    }
}