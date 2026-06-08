<x-guest-layout>
    @php
        $history = collect($priceHistory ?? []);
        $latest = $latestPrice ?? $history->last();

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
                return 'N/A';
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

        $symbol = strtoupper($coin->symbol);
        $name = $coin->name ?? $symbol;

        $currentPrice = data_get($latest, 'price_usd');
        $currentVolume = data_get($latest, 'volume_24h');
        $currentChange = data_get($latest, 'percent_change_24h');
        $currentFetchedAt = data_get($latest, 'fetched_at');

        $chartRows = $history->filter(fn ($row) => is_numeric($row->price_usd))->values();

        $chartWidth = 900;
        $chartHeight = 320;
        $paddingX = 36;
        $paddingY = 32;

        $prices = $chartRows->map(fn ($row) => (float) $row->price_usd);
        $minPrice = $prices->count() ? $prices->min() : 0;
        $maxPrice = $prices->count() ? $prices->max() : 0;
        $priceRange = max($maxPrice - $minPrice, 0.00000001);

        $points = [];

        if ($chartRows->count() === 1) {
            $points[] = [
                'x' => $chartWidth / 2,
                'y' => $chartHeight / 2,
                'price' => (float) $chartRows[0]->price_usd,
                'time' => $chartRows[0]->fetched_at,
            ];
        } elseif ($chartRows->count() > 1) {
            $count = $chartRows->count();

            foreach ($chartRows as $index => $row) {
                $x = $paddingX + ($index / ($count - 1)) * ($chartWidth - ($paddingX * 2));
                $normalized = ((float) $row->price_usd - $minPrice) / $priceRange;
                $y = ($chartHeight - $paddingY) - ($normalized * ($chartHeight - ($paddingY * 2)));

                $points[] = [
                    'x' => round($x, 2),
                    'y' => round($y, 2),
                    'price' => (float) $row->price_usd,
                    'time' => $row->fetched_at,
                ];
            }
        }

        $polyline = collect($points)->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');

        $firstPoint = collect($points)->first();
        $lastPoint = collect($points)->last();

        $firstTime = $chartRows->first()?->fetched_at;
        $lastTime = $chartRows->last()?->fetched_at;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-cyan-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <a href="{{ route('crypto.index') }}"
                       class="inline-flex items-center text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                        ← Quay lại bảng giá crypto
                    </a>

                    <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm text-cyan-200">
                        <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_14px_rgba(103,232,249,0.9)]"></span>
                        Coin market detail
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <div class="h-16 w-16 rounded-3xl bg-gradient-to-br from-slate-700 to-slate-950 border border-white/10 flex items-center justify-center text-lg font-black text-white shadow-lg shadow-cyan-500/10">
                            {{ mb_substr($symbol, 0, 3) }}
                        </div>

                        <div>
                            <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                                {{ $symbol }}
                            </h1>

                            <p class="mt-1 text-slate-400">
                                {{ $name }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-6 max-w-2xl text-slate-300 leading-relaxed">
                        Theo dõi giá mới nhất, biến động 24h, volume giao dịch và lịch sử giá của {{ $symbol }} trong hệ thống.
                    </p>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="text-sm text-slate-400">
                            Giá hiện tại
                        </div>

                        <div class="mt-2 text-4xl font-black text-white">
                            {{ $formatPrice($currentPrice) }}
                        </div>

                        <div class="mt-4">
                            @if (is_numeric($currentChange))
                                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold {{ (float) $currentChange >= 0 ? 'bg-emerald-400/10 text-emerald-300 ring-1 ring-emerald-400/20' : 'bg-rose-400/10 text-rose-300 ring-1 ring-rose-400/20' }}">
                                    {{ (float) $currentChange >= 0 ? '+' : '' }}{{ $formatPercent($currentChange) }} 24h
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold bg-slate-400/10 text-slate-300 ring-1 ring-slate-400/20">
                                    24h N/A
                                </span>
                            @endif
                        </div>

                        <div class="mt-6 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                            <div class="text-sm font-semibold text-cyan-200">
                                Cập nhật gần nhất
                            </div>

                            <div class="mt-1 text-sm text-slate-200">
                                {{ $formatDate($currentFetchedAt) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- STATS --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="text-sm text-slate-400">Giá USD</div>
                <div class="mt-2 text-3xl font-black text-white">{{ $formatPrice($currentPrice) }}</div>
                <div class="mt-2 text-xs text-slate-500">Nguồn từ bảng crypto_prices</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="text-sm text-slate-400">Biến động 24h</div>

                <div class="mt-2 text-3xl font-black {{ is_numeric($currentChange) && (float) $currentChange >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                    {{ is_numeric($currentChange) && (float) $currentChange >= 0 ? '+' : '' }}{{ $formatPercent($currentChange) }}
                </div>

                <div class="mt-2 text-xs text-slate-500">percent_change_24h</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="text-sm text-slate-400">Volume 24h</div>
                <div class="mt-2 text-3xl font-black text-white">{{ $formatLarge($currentVolume) }}</div>
                <div class="mt-2 text-xs text-slate-500">volume_24h</div>
            </div>
        </section>

        {{-- CHART --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 md:p-6 border-b border-white/10">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-white">
                        Biểu đồ giá {{ $symbol }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Dữ liệu được lấy từ các bản ghi lịch sử trong bảng crypto_prices.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                    {{ $chartRows->count() }} điểm dữ liệu
                </div>
            </div>

            @if ($chartRows->count() === 0)
                <div class="p-10 text-center">
                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                        <span class="text-3xl">📉</span>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-white">
                        Chưa có dữ liệu lịch sử giá
                    </h3>

                    <p class="mt-2 text-slate-400">
                        Hãy chạy command cập nhật giá crypto để hệ thống ghi nhận thêm dữ liệu theo thời gian.
                    </p>
                </div>
            @else
                <div class="p-5 md:p-6">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/60 p-4 overflow-x-auto">
                        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                             class="min-w-[760px] w-full h-[360px]"
                             role="img"
                             aria-label="Biểu đồ giá {{ $symbol }}">
                            {{-- Grid --}}
                            @for ($i = 0; $i <= 5; $i++)
                                @php
                                    $y = $paddingY + ($i / 5) * ($chartHeight - ($paddingY * 2));
                                @endphp

                                <line x1="{{ $paddingX }}"
                                      y1="{{ $y }}"
                                      x2="{{ $chartWidth - $paddingX }}"
                                      y2="{{ $y }}"
                                      stroke="rgba(255,255,255,0.08)"
                                      stroke-width="1" />
                            @endfor

                            @for ($i = 0; $i <= 8; $i++)
                                @php
                                    $x = $paddingX + ($i / 8) * ($chartWidth - ($paddingX * 2));
                                @endphp

                                <line x1="{{ $x }}"
                                      y1="{{ $paddingY }}"
                                      x2="{{ $x }}"
                                      y2="{{ $chartHeight - $paddingY }}"
                                      stroke="rgba(255,255,255,0.05)"
                                      stroke-width="1" />
                            @endfor

                            {{-- Gradient --}}
                            <defs>
                                <linearGradient id="priceLineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#818cf8" />
                                    <stop offset="50%" stop-color="#22d3ee" />
                                    <stop offset="100%" stop-color="#34d399" />
                                </linearGradient>
                            </defs>

                            @if ($chartRows->count() === 1 && $firstPoint)
                                <circle cx="{{ $firstPoint['x'] }}"
                                        cy="{{ $firstPoint['y'] }}"
                                        r="8"
                                        fill="#22d3ee" />

                                <text x="{{ $firstPoint['x'] }}"
                                      y="{{ $firstPoint['y'] - 18 }}"
                                      text-anchor="middle"
                                      fill="#e2e8f0"
                                      font-size="14"
                                      font-weight="700">
                                    {{ $formatPrice($firstPoint['price']) }}
                                </text>
                            @else
                                <polyline points="{{ $polyline }}"
                                          fill="none"
                                          stroke="url(#priceLineGradient)"
                                          stroke-width="4"
                                          stroke-linecap="round"
                                          stroke-linejoin="round" />

                                @if ($lastPoint)
                                    <circle cx="{{ $lastPoint['x'] }}"
                                            cy="{{ $lastPoint['y'] }}"
                                            r="7"
                                            fill="#22d3ee"
                                            stroke="#ffffff"
                                            stroke-width="2" />

                                    <text x="{{ max($lastPoint['x'] - 8, 80) }}"
                                          y="{{ max($lastPoint['y'] - 18, 20) }}"
                                          text-anchor="end"
                                          fill="#e2e8f0"
                                          font-size="14"
                                          font-weight="700">
                                        {{ $formatPrice($lastPoint['price']) }}
                                    </text>
                                @endif
                            @endif

                            {{-- Axis labels --}}
                            <text x="{{ $paddingX }}"
                                  y="{{ $chartHeight - 8 }}"
                                  fill="#94a3b8"
                                  font-size="12">
                                {{ $formatDate($firstTime) }}
                            </text>

                            <text x="{{ $chartWidth - $paddingX }}"
                                  y="{{ $chartHeight - 8 }}"
                                  text-anchor="end"
                                  fill="#94a3b8"
                                  font-size="12">
                                {{ $formatDate($lastTime) }}
                            </text>

                            <text x="{{ $paddingX }}"
                                  y="20"
                                  fill="#94a3b8"
                                  font-size="12">
                                Max: {{ $formatPrice($maxPrice) }}
                            </text>

                            <text x="{{ $paddingX }}"
                                  y="{{ $chartHeight - 28 }}"
                                  fill="#94a3b8"
                                  font-size="12">
                                Min: {{ $formatPrice($minPrice) }}
                            </text>
                        </svg>
                    </div>

                    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Giá thấp nhất trong dữ liệu</div>
                            <div class="mt-1 text-lg font-black text-white">{{ $formatPrice($minPrice) }}</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Giá cao nhất trong dữ liệu</div>
                            <div class="mt-1 text-lg font-black text-white">{{ $formatPrice($maxPrice) }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- HISTORY TABLE --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
            <div class="p-5 md:p-6 border-b border-white/10">
                <h2 class="text-2xl font-black text-white">
                    Lịch sử cập nhật
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Danh sách các lần ghi nhận giá gần nhất của {{ $symbol }}.
                </p>
            </div>

            @if ($history->count() === 0)
                <div class="p-8 text-slate-400">
                    Chưa có lịch sử cập nhật.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10">
                        <thead class="bg-slate-950/60">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Thời gian</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Giá</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">24h</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Volume 24h</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @foreach ($history->reverse()->values() as $row)
                                <tr class="hover:bg-white/[0.04] transition">
                                    <td class="px-5 py-4 text-sm text-slate-400">
                                        {{ $formatDate($row->fetched_at) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-bold text-white">
                                        {{ $formatPrice($row->price_usd) }}
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        @if (is_numeric($row->percent_change_24h))
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ (float) $row->percent_change_24h >= 0 ? 'bg-emerald-400/10 text-emerald-300 ring-1 ring-emerald-400/20' : 'bg-rose-400/10 text-rose-300 ring-1 ring-rose-400/20' }}">
                                                {{ (float) $row->percent_change_24h >= 0 ? '+' : '' }}{{ $formatPercent($row->percent_change_24h) }}
                                            </span>
                                        @else
                                            <span class="text-slate-500">N/A</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right text-slate-300">
                                        {{ $formatLarge($row->volume_24h) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div>
            <a href="{{ route('crypto.index') }}"
               class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                ← Quay lại bảng giá crypto
            </a>
        </div>
    </div>
</x-guest-layout>