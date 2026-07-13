<x-guest-layout>
    @php
        $areas = $application->expertise_areas ?? [];
        if (! is_array($areas)) {
            $areas = [];
        }

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
        ];

        $badge = $statusConfig[$application->status] ?? [
            'label' => strtoupper((string) $application->status),
            'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
            'dot' => 'bg-slate-300',
        ];

        $socialLinks = [
            'Website cá nhân' => $application->website_url,
            'LinkedIn' => $application->linkedin_url,
            'X / Twitter' => $application->x_url,
        ];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-indigo-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative p-6 md:p-10">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="min-w-0">
                        <a href="{{ route('admin.author-applications.index') }}"
                           class="inline-flex items-center text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                            ← Quay lại danh sách đơn
                        </a>

                        <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                            <span class="h-2 w-2 rounded-full bg-indigo-300 shadow-[0_0_14px_rgba(129,140,248,0.9)]"></span>
                            Author application detail
                        </div>

                        <h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight break-words [overflow-wrap:anywhere]">
                            {{ $application->full_name }}
                        </h1>

                        <p class="mt-3 max-w-3xl text-slate-300 leading-relaxed break-words [overflow-wrap:anywhere]">
                            {{ $application->headline ?: 'Ứng viên chưa cung cấp headline.' }}
                        </p>
                    </div>

                    <div class="shrink-0">
                        <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-bold {{ $badge['class'] }}">
                            <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                            {{ $badge['label'] }}
                        </span>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                        <div class="text-xs text-slate-500">Email</div>
                        <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                            {{ $application->user?->email ?? 'Không có email' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                        <div class="text-xs text-slate-500">Số năm kinh nghiệm</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            {{ $application->experience_years ?? 0 }} năm
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                        <div class="text-xs text-slate-500">Tài khoản hệ thống</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            ID: {{ $application->user?->id ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                        <div class="text-xs text-slate-500">Thời gian gửi</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            {{ $application->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- STATUS MESSAGES --}}
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

            @if (session('status') === 'application-already-reviewed')
                <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-5 py-4 text-yellow-100">
                    Đơn này đã được xử lý trước đó.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    <div class="font-semibold mb-2">Có lỗi xảy ra:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- PROFILE INFO --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <h2 class="text-xl font-black text-white">
                    Hồ sơ ứng viên
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Thông tin định danh và hồ sơ chuyên môn mà người dùng cung cấp.
                </p>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Tên công khai</div>
                        <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                            {{ $application->public_name ?: 'Không có' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Tên thật / tên hiển thị</div>
                        <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                            {{ $application->full_name }}
                        </div>
                    </div>

                    <div class="md:col-span-2 rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Lĩnh vực chuyên môn</div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @forelse ($areas as $area)
                                <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold text-cyan-100 break-words [overflow-wrap:anywhere]">
                                    {{ $area }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-500">Chưa có thông tin</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <h2 class="text-xl font-black text-white">
                    Liên kết xác minh
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Các kênh giúp admin đánh giá độ tin cậy của ứng viên.
                </p>

                <div class="mt-6 space-y-3">
                    @foreach ($socialLinks as $label => $url)
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">{{ $label }}</div>

                            <div class="mt-1 text-sm break-all">
                                @if ($url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-cyan-300 hover:text-cyan-200 font-semibold">
                                        {{ $url }}
                                    </a>
                                @else
                                    <span class="text-slate-500">Không có</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- EXPERIENCE + MOTIVATION --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-indigo-400/10 border border-indigo-400/20 flex items-center justify-center">
                        ✍️
                    </div>

                    <h2 class="text-xl font-black text-white">
                        Tóm tắt kinh nghiệm
                    </h2>
                </div>

                <div class="mt-5 rounded-2xl border border-white/10 bg-slate-950/50 p-5">
                    <p class="text-slate-300 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-8">
                        {{ $application->experience_summary ?: 'Ứng viên chưa cung cấp tóm tắt kinh nghiệm.' }}
                    </p>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-emerald-400/10 border border-emerald-400/20 flex items-center justify-center">
                        🎯
                    </div>

                    <h2 class="text-xl font-black text-white">
                        Lý do muốn trở thành tác giả
                    </h2>
                </div>

                <div class="mt-5 rounded-2xl border border-white/10 bg-slate-950/50 p-5">
                    <p class="text-slate-300 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-8">
                        {{ $application->motivation ?: 'Ứng viên chưa cung cấp lý do.' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- SAMPLE ARTICLE --}}
        <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-white">
                        Bài viết mẫu
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Admin nên đánh giá độ rõ ràng, tính chuyên môn, tính trung lập và rủi ro nội dung.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300">
                    Sample content
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-white/10 bg-slate-950/60 overflow-hidden">
                <div class="border-b border-white/10 p-5">
                    <h3 class="text-2xl font-black text-white break-words [overflow-wrap:anywhere]">
                        {{ $application->sample_article_title ?: 'Chưa có tiêu đề bài mẫu' }}
                    </h3>
                </div>

                <div class="p-5">
                    <div class="text-slate-300 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-8">
                        {{ $application->sample_article_content ?: 'Ứng viên chưa cung cấp nội dung bài viết mẫu.' }}
                    </div>
                </div>
            </div>
        </section>

        {{-- REVIEW INFORMATION --}}
        @if ($application->status !== 'pending')
            <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <h2 class="text-xl font-black text-white">
                    Thông tin xét duyệt
                </h2>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Người duyệt</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            {{ $application->reviewer?->name ?? 'Không có thông tin' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Thời gian duyệt</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            {{ $application->reviewed_at ? $application->reviewed_at->format('d/m/Y H:i') : 'Chưa có' }}
                        </div>
                    </div>
                </div>

                @if ($application->status === 'rejected')
                    <div class="mt-6 rounded-2xl bg-rose-400/10 border border-rose-400/20 p-5">
                        <div class="font-semibold text-rose-200">
                            Lý do từ chối
                        </div>

                        <p class="mt-2 text-rose-100 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-7">
                            {{ $application->rejection_reason ?: 'Không có lý do cụ thể.' }}
                        </p>
                    </div>
                @endif
            </section>
        @endif

        {{-- ADMIN ACTIONS --}}
        @if ($application->status === 'pending')
            <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div>
                        <h2 class="text-xl font-black text-white">
                            Hành động của admin
                        </h2>

                        <p class="mt-2 text-sm text-slate-400 max-w-2xl">
                            Hãy kiểm tra kỹ hồ sơ, kinh nghiệm, lĩnh vực chuyên môn và bài viết mẫu trước khi cấp quyền AUTHOR.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.author-applications.approve', $application) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="w-full lg:w-auto rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                            Duyệt và cấp quyền AUTHOR
                        </button>
                    </form>
                </div>

                <div class="mt-6 rounded-2xl bg-yellow-400/10 border border-yellow-400/20 p-5 text-yellow-100 text-sm leading-relaxed">
                    Sau khi duyệt, tài khoản người dùng sẽ được cấp quyền AUTHOR và có thể tạo bài viết. Bài viết của AUTHOR vẫn cần admin kiểm duyệt trước khi hiển thị công khai.
                </div>

                <form method="POST"
                      action="{{ route('admin.author-applications.reject', $application) }}"
                      class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="rejection_reason" class="block font-semibold text-sm text-slate-200">
                            Lý do từ chối
                        </label>

                        <textarea id="rejection_reason"
                                  name="rejection_reason"
                                  rows="5"
                                  class="mt-2 block w-full rounded-2xl border-white/10 bg-slate-950/70 text-slate-200 shadow-sm focus:border-cyan-400 focus:ring-cyan-400/30"
                                  placeholder="VD: Hồ sơ chưa thể hiện đủ kinh nghiệm, bài viết mẫu còn quá ngắn hoặc chưa phù hợp với định hướng nội dung..."
                                  required>{{ old('rejection_reason') }}</textarea>

                        @error('rejection_reason')
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="rounded-2xl border border-rose-400/20 bg-rose-500/15 px-5 py-3 text-sm font-bold text-rose-100 hover:bg-rose-500/25 transition">
                        Từ chối đơn
                    </button>
                </form>
            </section>
        @endif

        <div>
            <a href="{{ route('admin.author-applications.index') }}"
               class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                ← Quay lại danh sách đơn
            </a>
        </div>
    </div>
</x-guest-layout>