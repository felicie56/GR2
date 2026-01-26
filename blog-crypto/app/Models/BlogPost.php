<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'thumbnail',
        'status',
    ];

    // Tác giả
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Comment cho bài blog
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Reaction cho bài blog
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }
}
