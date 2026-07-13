<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsRelatedLink extends Model
{
    protected $fillable = [
        'news_id',
        'related_news_id',
        'score',
        'display_order',
        'paragraph_index',
        'matched_keywords',
        'reason',
    ];

    protected $casts = [
        'score' => 'float',
        'display_order' => 'integer',
        'paragraph_index' => 'integer',
        'matched_keywords' => 'array',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    public function relatedNews(): BelongsTo
    {
        return $this->belongsTo(News::class, 'related_news_id');
    }
}