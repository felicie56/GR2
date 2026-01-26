<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPrice extends Model
{
    use HasFactory;

    public $timestamps = false;   // ⬅⬅⬅ THÊM DÒNG NÀY

    protected $fillable = [
        'coin_id',
        'price_usd',
        'volume_24h',
        'percent_change_24h',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];

    public function coin(): BelongsTo
    {
        return $this->belongsTo(CryptoCoin::class, 'coin_id');
    }
}
