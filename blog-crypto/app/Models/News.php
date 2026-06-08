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
    ];

    protected $casts = [
        'is_auto' => 'boolean',
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'news_id')->latest();
    }
}