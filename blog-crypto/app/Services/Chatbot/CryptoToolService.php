<?php

namespace App\Services\Chatbot;

use App\Models\CryptoCoin;
use Illuminate\Support\Str;

class CryptoToolService
{
    /** @return array<string, mixed> */
    public function quote(string $query): array
    {
        $coin = $this->findCoin($query);

        if (! $coin) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đồng coin phù hợp trong database website.',
                'query' => $query,
            ];
        }

        $coin->loadMissing('latestPrice');
        $price = $coin->latestPrice;

        if (! $price) {
            return [
                'success' => false,
                'message' => 'Đã tìm thấy coin nhưng chưa có dữ liệu giá.',
                'coin' => [
                    'name' => $coin->name,
                    'symbol' => strtoupper((string) $coin->symbol),
                ],
            ];
        }

        return [
            'success' => true,
            'coin' => [
                'id' => $coin->id,
                'name' => $coin->name,
                'symbol' => strtoupper((string) $coin->symbol),
                'rank' => $coin->rank,
            ],
            'price_usd' => (float) $price->price_usd,
            'percent_change_24h' => $price->percent_change_24h !== null
                ? (float) $price->percent_change_24h
                : null,
            'volume_24h' => $price->volume_24h !== null
                ? (float) $price->volume_24h
                : null,
            'fetched_at' => $price->fetched_at?->toIso8601String(),
            'public_url' => route(
                'crypto.show',
                strtolower((string) $coin->symbol)
            ),
            'notice' => 'Dữ liệu lấy từ database CryptoBlog, không phải giá giao dịch trực tiếp.',
        ];
    }

    /** @return array<string, mixed> */
    public function history(string $query, int $hours = 24): array
    {
        $coin = $this->findCoin($query);

        if (! $coin) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đồng coin phù hợp trong database website.',
                'query' => $query,
            ];
        }

        $hours = max(1, min($hours, 168));

        $prices = $coin->prices()
            ->where('fetched_at', '>=', now()->subHours($hours))
            ->orderBy('fetched_at')
            ->limit(200)
            ->get(['price_usd', 'percent_change_24h', 'fetched_at']);

        if ($prices->isEmpty()) {
            return [
                'success' => false,
                'message' => "Chưa có dữ liệu lịch sử trong {$hours} giờ gần đây.",
                'coin' => [
                    'name' => $coin->name,
                    'symbol' => strtoupper((string) $coin->symbol),
                ],
            ];
        }

        $values = $prices->pluck('price_usd')
            ->map(fn ($value) => (float) $value);

        $first = (float) $values->first();
        $last = (float) $values->last();
        $changePercent = $first > 0
            ? (($last - $first) / $first) * 100
            : null;

        return [
            'success' => true,
            'coin' => [
                'name' => $coin->name,
                'symbol' => strtoupper((string) $coin->symbol),
            ],
            'period_hours' => $hours,
            'sample_count' => $prices->count(),
            'first_price_usd' => $first,
            'latest_price_usd' => $last,
            'minimum_price_usd' => $values->min(),
            'maximum_price_usd' => $values->max(),
            'change_percent_in_period' => $changePercent,
            'started_at' => $prices->first()?->fetched_at?->toIso8601String(),
            'ended_at' => $prices->last()?->fetched_at?->toIso8601String(),
            'public_url' => route(
                'crypto.show',
                strtolower((string) $coin->symbol)
            ),
        ];
    }

    private function findCoin(string $query): ?CryptoCoin
    {
        $query = trim($query);

        if ($query === '') {
            return null;
        }

        $normalized = Str::lower($query);
        $symbol = Str::upper(preg_replace('/[^a-zA-Z0-9]/', '', $query) ?? '');

        return CryptoCoin::query()
            ->with('latestPrice')
            ->where(function ($builder) use ($normalized, $symbol) {
                if ($symbol !== '') {
                    $builder->whereRaw('UPPER(symbol) = ?', [$symbol]);
                }

                $builder->orWhereRaw('LOWER(name) = ?', [$normalized])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereRaw('LOWER(symbol) LIKE ?', ['%' . $normalized . '%']);
            })
            ->orderByRaw(
                'CASE WHEN UPPER(symbol) = ? THEN 0 ELSE 1 END',
                [$symbol]
            )
            ->orderByRaw('COALESCE(`rank`, 999999) ASC')
            ->first();
    }
}