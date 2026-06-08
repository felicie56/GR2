<?php

namespace App\Http\Controllers;

use App\Models\CryptoCoin;

class CryptoController extends Controller
{
    public function index()
    {
        $coins = CryptoCoin::with('latestPrice')
            ->orderBy('rank')
            ->get();

        return view('crypto.index', compact('coins'));
    }

    public function show(string $symbol)
    {
        $coin = CryptoCoin::with('latestPrice')
            ->whereRaw('LOWER(symbol) = ?', [strtolower($symbol)])
            ->firstOrFail();

        /*
         * Lấy 80 bản ghi mới nhất trước,
         * sau đó reverse lại để biểu đồ chạy từ cũ -> mới.
         */
        $priceHistory = $coin->prices()
            ->whereNotNull('fetched_at')
            ->whereNotNull('price_usd')
            ->orderByDesc('fetched_at')
            ->limit(80)
            ->get()
            ->reverse()
            ->values();

        /*
         * Giá hiện tại phải ưu tiên latestPrice,
         * vì latestPrice là bản ghi mới nhất theo fetched_at.
         */
        $latestPrice = $coin->latestPrice;

        if (! $latestPrice && $priceHistory->count() > 0) {
            $latestPrice = $priceHistory->last();
        }

        return view('crypto.show', compact('coin', 'priceHistory', 'latestPrice'));
    }
}