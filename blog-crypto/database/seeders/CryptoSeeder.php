<?php

namespace Database\Seeders;

use App\Models\CryptoCoin;
use App\Models\CryptoPrice;
use Illuminate\Database\Seeder;

class CryptoSeeder extends Seeder
{
    public function run(): void
    {
        $coins = [
            [
                'symbol' => 'BTC',
                'name'   => 'Bitcoin',
                'rank'   => 1,
                'price'  => 100000,
                'change' => 5.23,
                'volume' => 35000000000,
            ],
            [
                'symbol' => 'ETH',
                'name'   => 'Ethereum',
                'rank'   => 2,
                'price'  => 4500,
                'change' => -2.15,
                'volume' => 18000000000,
            ],
            [
                'symbol' => 'BNB',
                'name'   => 'Binance Coin',
                'rank'   => 3,
                'price'  => 650,
                'change' => 1.75,
                'volume' => 3000000000,
            ],
        ];

        foreach ($coins as $data) {
            $coin = CryptoCoin::firstOrCreate(
                ['symbol' => $data['symbol']],
                [
                    'name' => $data['name'],
                    'rank' => $data['rank'],
                ]
            );

            CryptoPrice::create([
                'coin_id'            => $coin->id,
                'price_usd'          => $data['price'],
                'volume_24h'         => $data['volume'],
                'percent_change_24h' => $data['change'],
                'fetched_at'         => now(),
            ]);
        }
    }
}
