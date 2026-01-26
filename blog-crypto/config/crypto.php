<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Coin mapping: DB symbol => CoinGecko coin id
    |--------------------------------------------------------------------------
    | Vì bảng crypto_coins của bạn chỉ lưu symbol (BTC/ETH/BNB),
    | nên ta mapping sang CoinGecko "id" để gọi API.
    */
    'coingecko_ids' => [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'BNB' => 'binancecoin',
        // thêm coin ở đây nếu muốn:
        // 'SOL' => 'solana',
        // 'XRP' => 'ripple',
    ],

    // đồng quy đổi
    'vs_currency' => 'usd',
];
