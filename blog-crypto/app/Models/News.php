<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'thumbnail',
        'source',
        'source_url',
        'source_feed',
        'external_id',
        'is_auto',
        'category_id',
        'published_at',
        'fetched_at',
        'related_links_generated_at',
    ];

    protected $casts = [
        'is_auto' => 'boolean',
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
        'related_links_generated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'news_id')->latest();
    }

    /**
     * Các liên kết từ bài hiện tại tới những bài cũ có liên quan.
     */
    public function relatedLinks(): HasMany
    {
        return $this->hasMany(NewsRelatedLink::class, 'news_id')
            ->orderBy('display_order')
            ->orderByDesc('score');
    }

    /**
     * Những bài khác đang trỏ tới bài hiện tại.
     */
    public function incomingRelatedLinks(): HasMany
    {
        return $this->hasMany(NewsRelatedLink::class, 'related_news_id')
            ->orderByDesc('score');
    }
}