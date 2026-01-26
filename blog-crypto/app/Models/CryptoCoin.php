<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CryptoCoin extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'rank',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(CryptoPrice::class, 'coin_id');
    }

    // Giá mới nhất (dựa trên fetched_at)
    public function latestPrice(): HasOne
    {
        return $this->hasOne(CryptoPrice::class, 'coin_id')->latestOfMany('fetched_at');
    }
}
