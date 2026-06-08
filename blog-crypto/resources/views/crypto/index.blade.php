<x-guest-layout>
    @section('title', 'Giá Crypto - CryptoBlog')
    @php
        $sourceItems = $coins
            ?? $cryptoCoins
            ?? $prices
            ?? $cryptoPrices
            ?? $marketData
            ?? collect();

        $isPaginator = is_object($sourceItems) && method_exists($sourceItems, 'getCollection');
        $coinItems = $isPaginator ? $sourceItems->getCollection() : collect($sourceItems);
        $totalItems = $isPaginator && method_exists($sourceItems, 'total') ? $sourceItems->total() : $coinItems->count();

        $getValue = function ($item, array $keys, $default = null) {
            foreach ($keys as $key) {
                $value = data_get($item, $key);

                if (! is_null($value) && $value !== '') {
                    return $value;
                }
            }

            return $default;
        };

        $formatPrice = function ($value) {
            if (! is_numeric($value)) {
                return 'N/A';
            }

            $value = (float) $value;

            if ($value >= 1) {
                return '$' . number_format($value, 2);
            }

            return '$' . rtrim(rtrim(number_format($value, 8), '0'), '.');
        };

        $formatLarge = function ($value) {
            if (! is_numeric($value)) {
                return 'N/A';
            }

            $value = (float) $value;

            if ($value >= 1000000000000) {
                return '$' . number_format($value / 1000000000000, 2) . 'T';
            }

            if ($value >= 1000000000) {
                return '$' . number_format($value / 1000000000, 2) . 'B';
            }

            if ($value >= 1000000) {
                return '$' . number_format($value / 1000000, 2) . 'M';
            }

            if ($value >= 1000) {
                return '$' . number_format($value / 1000, 2) . 'K';
            }

            return '$' . number_format($value, 2);
        };

        $formatPercent = function ($value) {
            if (! is_numeric($value)) {
                return null;
            }

            return number_format((float) $value, 2) . '%';
        };

        $formatDate = function ($value) {
            if (! $value) {
                return 'Chưa rõ';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                return $value;
            }
        };

        $topCoins = $coinItems->take(3);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-emerald-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-20 -right-20 h-72 w-72 rounded-full bg-emerald-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-600/20 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10 lg:p-12">
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-sm text-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_14px_rgba(110,231,183,0.9)]"></span>
                        Live crypto market
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
                        Theo dõi
                        <span class="bg-gradient-to-r from-emerald-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                            giá Crypto
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-base md:text-lg text-slate-300 leading-relaxed">
                        Bảng giá các đồng tiền điện tử trong hệ thống, hỗ trợ người dùng theo dõi giá, volume 24h và biến động thị trường.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('news.index') }}"
                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                            Xem tin thị trường
                        </a>

                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Đọc phân tích
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Coin đang theo dõi</div>
                                <div class="mt-1 text-3xl font-black text-white">{{ $totalItems }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 17L9 12L13 15L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20 7H15M20 7V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($topCoins as $item)
                                @php
                                    $symbol = strtoupper((string) $getValue($item, ['symbol', 'coin_symbol', 'ticker', 'code'], 'N/A'));
                                    $name = $getValue($item, ['name', 'coin_name', 'full_name'], $symbol);
                                    $price = $getValue($item, ['latestPrice.price_usd', 'latest_price.price_usd', 'price_usd', 'current_price', 'price']);
                                    $change = $getValue($item, ['latestPrice.percent_change_24h', 'latest_price.percent_change_24h', 'percent_change_24h', 'price_change_percentage_24h', 'change_24h']);
                                    $detailUrl = Route::has('crypto.show') && $symbol !== 'N/A' ? route('crypto.show', $symbol) : '#';
                                @endphp

                                <a href="{{ $detailUrl }}"
                                   class="block rounded-2xl border border-white/10 bg-white/[0.04] p-3 hover:bg-white/[0.08] hover:border-cyan-400/30 transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 border border-white/10 flex items-center justify-center text-xs font-black text-white">
                                                {{ mb_substr($symbol, 0, 3) }}
                                            </div>

                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-white truncate">{{ $symbol }}</div>
                                                <div class="text-xs text-slate-500 truncate">{{ $name }}</div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="text-sm font-bold text-white">{{ $formatPrice($price) }}</div>

                                            @if (! is_null($formatPercent($change)))
                                                <div class="text-xs {{ (float) $change >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                                                    {{ (float) $change >= 0 ? '+' : '' }}{{ $formatPercent($change) }}
                                                </div>
                                            @else
                                                <div class="text-xs text-slate-500">24h N/A</div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4 text-sm text-slate-400">
                                    Chưa có dữ liệu coin. Hãy chạy command cập nhật giá crypto nếu hệ thống đã có.
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-5 rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4">
                            <div class="text-sm font-semibold text-yellow-200">
                                Lưu ý dữ liệu
                            </div>
                            <p class="mt-1 text-sm text-slate-300">
                                Giá crypto biến động liên tục. Thông tin trên hệ thống chỉ mang tính tham khảo, không phải lời khuyên đầu tư.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- MARKET TABLE --}}
        <section class="mt-8 rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 md:p-6 border-b border-white/10">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-white">
                        Bảng giá thị trường
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Bấm vào từng coin để xem biểu đồ giá theo thời gian.
                    </p>
                </div>

                <div class="flex items-center gap-2 rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_14px_rgba(52,211,153,0.8)]"></span>
                    <span class="text-sm text-slate-300">
                        Market data
                    </span>
                </div>
            </div>

            @if ($coinItems->count() === 0)
                <div class="p-10 text-center">
                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-900 border border-white/10 flex items-center justify-center">
                        <span class="text-3xl">₿</span>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-white">
                        Chưa có dữ liệu giá crypto
                    </h3>

                    <p class="mt-2 text-slate-400">
                        Hãy kiểm tra bảng dữ liệu crypto hoặc chạy command cập nhật giá nếu project đã hỗ trợ.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10">
                        <thead class="bg-slate-950/60">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Coin</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Giá</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">24h</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Volume 24h</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Cập nhật giá</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @foreach ($coinItems as $item)
                                @php
                                    $symbol = strtoupper((string) $getValue($item, ['symbol', 'coin_symbol', 'ticker', 'code'], 'N/A'));
                                    $name = $getValue($item, ['name', 'coin_name', 'full_name'], $symbol);
                                    $price = $getValue($item, ['latestPrice.price_usd', 'latest_price.price_usd', 'price_usd', 'current_price', 'price']);
                                    $change = $getValue($item, ['latestPrice.percent_change_24h', 'latest_price.percent_change_24h', 'percent_change_24h', 'price_change_percentage_24h', 'change_24h']);
                                    $volume = $getValue($item, ['latestPrice.volume_24h', 'latest_price.volume_24h', 'volume_24h', 'volume']);
                                    $updatedAt = $getValue($item, ['latestPrice.fetched_at', 'latest_price.fetched_at', 'fetched_at']);
                                    $image = $getValue($item, ['image', 'logo', 'icon_url', 'image_url']);
                                    $detailUrl = Route::has('crypto.show') && $symbol !== 'N/A' ? route('crypto.show', $symbol) : '#';
                                @endphp

                                <tr onclick="window.location='{{ $detailUrl }}'"
                                    class="group hover:bg-white/[0.06] transition cursor-pointer">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($image)
                                                <img src="{{ $image }}"
                                                     alt="{{ $symbol }}"
                                                     class="h-10 w-10 rounded-full bg-slate-900 border border-white/10 object-cover">
                                            @else
                                                <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-950 border border-white/10 flex items-center justify-center text-xs font-black text-white">
                                                    {{ mb_substr($symbol, 0, 3) }}
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <a href="{{ $detailUrl }}"
                                                   class="font-bold text-white hover:text-cyan-300 transition"
                                                   onclick="event.stopPropagation()">
                                                    {{ $symbol }}
                                                </a>

                                                <div class="text-sm text-slate-500 truncate max-w-[260px]">
                                                    {{ $name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <div class="font-bold text-white">
                                            {{ $formatPrice($price) }}
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        @if (! is_null($formatPercent($change)))
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ (float) $change >= 0 ? 'bg-emerald-400/10 text-emerald-300 ring-1 ring-emerald-400/20' : 'bg-rose-400/10 text-rose-300 ring-1 ring-rose-400/20' }}">
                                                {{ (float) $change >= 0 ? '+' : '' }}{{ $formatPercent($change) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-slate-500">N/A</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right text-slate-300">
                                        {{ $formatLarge($volume) }}
                                    </td>

                                    <td class="px-5 py-4 text-right text-sm text-slate-500">
                                        {{ $formatDate($updatedAt) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($isPaginator)
                    <div class="p-5 border-t border-white/10">
                        {{ $sourceItems->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-guest-layout>