<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CryptoCoin extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'rank',
        'image',
        'logo',
        'icon_url',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(CryptoPrice::class, 'coin_id');
    }

    public function latestPrice(): HasOne
    {
        return $this->hasOne(CryptoPrice::class, 'coin_id')
            ->ofMany('fetched_at', 'max');
    }
}