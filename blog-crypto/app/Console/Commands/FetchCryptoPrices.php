<?php

namespace App\Console\Commands;

use App\Models\CryptoCoin;
use App\Models\CryptoPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchCryptoPrices extends Command
{
    protected $signature = 'crypto:fetch-prices {--force : Fetch even if fetched recently}';
    protected $description = 'Fetch crypto prices from CoinGecko and store into database';

    public function handle(): int
{
    $vs = 'usd';
    $perPage = 100;

    $this->info("Fetching TOP {$perPage} coins from CoinGecko...");

    $response = Http::withoutVerifying()
    ->retry(3, 500)
    ->timeout(20)
    ->get('https://api.coingecko.com/api/v3/coins/markets', [
        'vs_currency' => 'usd',
        'order' => 'market_cap_desc',
        'per_page' => 100,
        'page' => 1,
        'sparkline' => 'false',
        'price_change_percentage' => '24h',
    ]);

    if (!$response->ok()) {
        $this->error('CoinGecko request failed: HTTP ' . $response->status());
        $this->line($response->body());
        return self::FAILURE;
    }

    $rows = $response->json();
    if (!is_array($rows)) {
        $this->error('Unexpected response format from CoinGecko.');
        return self::FAILURE;
    }

    $now = now();
    $inserted = 0;

    foreach ($rows as $row) {
        // CoinGecko fields
        $symbol = strtoupper($row['symbol'] ?? '');
        $name = $row['name'] ?? null;
        $rank = $row['market_cap_rank'] ?? null;

        if (!$symbol || !$name) {
            continue;
        }

        // 1) Upsert coin vào crypto_coins
        $coin = \App\Models\CryptoCoin::updateOrCreate(
            ['symbol' => $symbol],
            [
                'name' => $name,
                'rank' => $rank ?? 9999,
            ]
        );

        // 2) Insert snapshot giá vào crypto_prices
        \App\Models\CryptoPrice::create([
            'coin_id' => $coin->id,
            'price_usd' => (string) ($row['current_price'] ?? 0),
            'volume_24h' => isset($row['total_volume']) ? (string) $row['total_volume'] : null,
            'percent_change_24h' => $row['price_change_percentage_24h'] ?? null,
            'fetched_at' => $now,
        ]);

        $inserted++;
    }

    $this->info("Done. Upserted coins and inserted {$inserted} price rows at {$now}.");
    return self::SUCCESS;
}

}
