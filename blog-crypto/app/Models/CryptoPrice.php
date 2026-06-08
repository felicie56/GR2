<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPrice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'coin_id',
        'price_usd',
        'volume_24h',
        'percent_change_24h',
        'fetched_at',
    ];

    protected $casts = [
        'price_usd' => 'decimal:8',
        'volume_24h' => 'decimal:2',
        'percent_change_24h' => 'float',
        'fetched_at' => 'datetime',
    ];

    public function coin(): BelongsTo
    {
        return $this->belongsTo(CryptoCoin::class, 'coin_id');
    }
}