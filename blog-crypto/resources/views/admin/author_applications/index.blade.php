<x-guest-layout>
    @php
        $status = $status ?? request('status', 'pending');

        $statusConfig = [
            'pending' => [
                'label' => 'Chờ duyệt',
                'class' => 'bg-yellow-400/10 text-yellow-200 border-yellow-400/20',
                'dot' => 'bg-yellow-300',
            ],
            'approved' => [
                'label' => 'Đã duyệt',
                'class' => 'bg-emerald-400/10 text-emerald-200 border-emerald-400/20',
                'dot' => 'bg-emerald-300',
            ],
            'rejected' => [
                'label' => 'Đã từ chối',
                'class' => 'bg-rose-400/10 text-rose-200 border-rose-400/20',
                'dot' => 'bg-rose-300',
            ],
            'all' => [
                'label' => 'Tất cả',
                'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
                'dot' => 'bg-slate-300',
            ],
        ];

        $statusBadge = function ($value) use ($statusConfig) {
            return $statusConfig[$value] ?? [
                'label' => strtoupper((string) $value),
                'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
                'dot' => 'bg-slate-300',
            ];
        };

        $applicationsCount = method_exists($applications, 'total') ? $applications->total() : $applications->count();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-indigo-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                        <span class="h-2 w-2 rounded-full bg-indigo-300 shadow-[0_0_14px_rgba(129,140,248,0.9)]"></span>
                        Author onboarding review
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Duyệt đơn đăng ký
                        <span class="bg-gradient-to-r from-indigo-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">
                            tác giả
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Xem xét hồ sơ, kinh nghiệm, lĩnh vực chuyên môn và bài viết mẫu của người dùng trước khi cấp quyền AUTHOR.
                    </p>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Kết quả hiện tại</div>
                                <div class="mt-1 text-4xl font-black text-white">{{ $applicationsCount }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M4.5 20C5.7 16.8 8.4 15 12 15C15.6 15 18.3 16.8 19.5 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                            <div class="text-sm font-semibold text-cyan-200">
                                Bộ lọc đang xem
                            </div>
                            <div class="mt-1 text-sm text-slate-200">
                                {{ $statusConfig[$status]['label'] ?? strtoupper($status) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FLASH MESSAGES --}}
        <section class="space-y-3">
            @if (session('success'))
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-5 py-4 text-yellow-100">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('status') === 'author-application-approved')
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                    Đã duyệt đơn và cấp quyền AUTHOR cho người dùng.
                </div>
            @endif

            @if (session('status') === 'author-application-rejected')
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    Đã từ chối đơn đăng ký tác giả.
                </div>
            @endif

            @if (session('status') === 'application-already-reviewed')
                <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-5 py-4 text-yellow-100">
                    Đơn này đã được xử lý trước đó.
                </div>
            @endif
        </section>

        {{-- FILTER TABS --}}
        <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-4">
            <div class="flex flex-wrap gap-3">
                @foreach (['pending', 'approved', 'rejected', 'all'] as $tab)
                    @php
                        $config = $statusConfig[$tab];
                        $active = $status === $tab;
                    @endphp

                    <a href="{{ route('admin.author-applications.index', ['status' => $tab]) }}"
                       class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2 text-sm font-semibold transition
                            {{ $active ? $config['class'] : 'border-white/10 bg-slate-950/40 text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <span class="h-2 w-2 rounded-full {{ $active ? $config['dot'] : 'bg-slate-500' }}"></span>
                        {{ $config['label'] }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- APPLICATION LIST --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 md:p-6 border-b border-white/10">
                <div>
                    <h2 class="text-2xl font-black text-white">
                        Danh sách hồ sơ
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Click vào từng hồ sơ để xem chi tiết và thực hiện phê duyệt hoặc từ chối.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                    Tổng: <span class="font-bold text-white">{{ $applicationsCount }}</span>
                </div>
            </div>

            {{-- Desktop table --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10">
                    <thead class="bg-slate-950/60">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Người gửi
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Headline
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Kinh nghiệm
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Trạng thái
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Thao tác
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($applications as $application)
                            @php
                                $badge = $statusBadge($application->status);

                                $areas = $application->expertise_areas ?? [];
                                if (! is_array($areas)) {
                                    $areas = [];
                                }
                            @endphp

                            <tr class="hover:bg-white/[0.04] transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-start gap-3">
                                        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm font-black text-white shrink-0">
                                            {{ strtoupper(mb_substr($application->full_name ?: 'A', 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-white break-words [overflow-wrap:anywhere]">
                                                {{ $application->full_name }}
                                            </div>

                                            <div class="mt-1 text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                                {{ $application->user?->email ?? 'Không có email' }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500">
                                                Gửi lúc: {{ $application->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-300 max-w-xs">
                                    <div class="break-words [overflow-wrap:anywhere]">
                                        {{ $application->headline ?: 'Chưa có headline' }}
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-sm">
                                    <div class="font-semibold text-white">
                                        {{ $application->experience_years ?? 0 }} năm
                                    </div>

                                    @if (! empty($areas))
                                        <div class="mt-2 flex flex-wrap gap-1.5 max-w-xs">
                                            @foreach (array_slice($areas, 0, 3) as $area)
                                                <span class="rounded-full border border-white/10 bg-slate-950/60 px-2 py-1 text-xs text-slate-300 break-words [overflow-wrap:anywhere]">
                                                    {{ $area }}
                                                </span>
                                            @endforeach

                                            @if (count($areas) > 3)
                                                <span class="rounded-full border border-white/10 bg-slate-950/60 px-2 py-1 text-xs text-slate-500">
                                                    +{{ count($areas) - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-1 text-xs text-slate-500">
                                            Chưa có lĩnh vực
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $badge['class'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('admin.author-applications.show', $application) }}"
                                       class="inline-flex items-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                        Xem chi tiết →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                                        <span class="text-3xl">📝</span>
                                    </div>

                                    <h3 class="mt-5 text-xl font-bold text-white">
                                        Chưa có đơn đăng ký tác giả nào
                                    </h3>

                                    <p class="mt-2 text-slate-400">
                                        Khi người dùng gửi đơn, hồ sơ sẽ xuất hiện tại đây.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="lg:hidden divide-y divide-white/10">
                @forelse ($applications as $application)
                    @php
                        $badge = $statusBadge($application->status);

                        $areas = $application->expertise_areas ?? [];
                        if (! is_array($areas)) {
                            $areas = [];
                        }
                    @endphp

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm font-black text-white shrink-0">
                                    {{ strtoupper(mb_substr($application->full_name ?: 'A', 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <div class="font-bold text-white break-words [overflow-wrap:anywhere]">
                                        {{ $application->full_name }}
                                    </div>

                                    <div class="mt-1 text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                        {{ $application->user?->email ?? 'Không có email' }}
                                    </div>
                                </div>
                            </div>

                            <span class="shrink-0 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $badge['class'] }}">
                                <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $badge['label'] }}
                            </span>
                        </div>

                        <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Headline</div>
                            <div class="mt-1 text-sm text-slate-300 break-words [overflow-wrap:anywhere]">
                                {{ $application->headline ?: 'Chưa có headline' }}
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-slate-400">
                                {{ $application->experience_years ?? 0 }} năm kinh nghiệm
                            </div>

                            <a href="{{ route('admin.author-applications.show', $application) }}"
                               class="inline-flex items-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                Xem chi tiết →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                            <span class="text-3xl">📝</span>
                        </div>

                        <h3 class="mt-5 text-xl font-bold text-white">
                            Chưa có đơn đăng ký tác giả nào
                        </h3>

                        <p class="mt-2 text-slate-400">
                            Khi người dùng gửi đơn, hồ sơ sẽ xuất hiện tại đây.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Pagination --}}
        <div>
            {{ $applications->links() }}
        </div>
    </div>
</x-guest-layout>