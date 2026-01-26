<x-guest-layout>
    <div class="max-w-6xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-white">Giá tiền điện tử</h1>
            <p class="text-sm text-gray-400">
                (Dữ liệu demo – sẽ kết nối API sau)
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-200">
                <thead class="bg-slate-800 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Coin</th>
                        <th class="px-4 py-2">Giá (USD)</th>
                        <th class="px-4 py-2">24h %</th>
                        <th class="px-4 py-2">Volume 24h</th>
                        <th class="px-4 py-2">Cập nhật lúc</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coins as $coin)
                        @php
                            $p = $coin->latestPrice;
                        @endphp
                        <tr class="border-b border-slate-800 bg-slate-900/60">
                            <td class="px-4 py-2">{{ $coin->rank }}</td>
                            <td class="px-4 py-2">
                                <span class="font-semibold">{{ $coin->symbol }}</span>
                                <span class="text-gray-400">– {{ $coin->name }}</span>
                            </td>
                            <td class="px-4 py-2">
                                @if($p)
                                    ${{ number_format($p->price_usd, 2) }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($p && !is_null($p->percent_change_24h))
                                    <span class="{{ $p->percent_change_24h >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ number_format($p->percent_change_24h, 2) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($p && !is_null($p->volume_24h))
                                    ${{ number_format($p->volume_24h, 0) }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($p)
                                    {{ $p->fetched_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-400">
                                Chưa có dữ liệu giá coin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-guest-layout>
