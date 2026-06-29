@section('title', 'Chi tiết đơn đăng ký tác giả - CryptoBlog')

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

        $formatDate = function ($value) {
            if (! $value) {
                return 'Chưa có';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                return $value;
            }
        };
    @endphp

    <style>
        .admin-review-content,
        .admin-review-content * {
            text-align: left !important;
            text-indent: 0 !important;
        }

        .admin-review-content {
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.9;
        }
    </style>

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
                    <a href="{{ route('admin.author-applications.index') }}"
                       class="inline-flex text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                        ← Quay lại danh sách đơn
                    </a>

                    <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                        <span class="h-2 w-2 rounded-full bg-indigo-300 shadow-[0_0_14px_rgba(129,140,248,0.9)]"></span>
                        Author application detail
                    </div>

                    <h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight break-words [overflow-wrap:anywhere]">
                        {{ $application->full_name ?: 'Hồ sơ tác giả' }}
                    </h1>

                    <p class="mt-4 max-w-3xl text-slate-300 leading-relaxed text-left">
                        Kiểm tra hồ sơ, kinh nghiệm, lĩnh vực chuyên môn và bài viết mẫu trước khi cấp quyền AUTHOR cho người dùng.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $badge['class'] }}">
                            <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                            {{ $badge['label'] }}
                        </span>

                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                            Gửi lúc: {{ $formatDate($application->created_at) }}
                        </span>

                        @if ($application->reviewed_at)
                            <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                Xử lý lúc: {{ $formatDate($application->reviewed_at) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm text-slate-400">Người gửi</div>
                                <div class="mt-1 text-xl font-black text-white break-words [overflow-wrap:anywhere]">
                                    {{ $application->user?->name ?? $application->full_name ?? 'Không rõ' }}
                                </div>
                                <div class="mt-1 text-sm text-slate-500 break-words [overflow-wrap:anywhere]">
                                    {{ $application->user?->email ?? 'Không có email' }}
                                </div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center text-xl font-black text-white shrink-0">
                                {{ strtoupper(mb_substr($application->full_name ?: 'A', 0, 1)) }}
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 text-sm">
                            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                <div class="text-slate-500">Bút danh</div>
                                <div class="mt-1 font-bold text-white break-words [overflow-wrap:anywhere]">
                                    {{ $application->public_name ?: 'Chưa có' }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                <div class="text-slate-500">Số năm kinh nghiệm</div>
                                <div class="mt-1 font-bold text-white">
                                    {{ $application->experience_years ?? 0 }} năm
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FLASH --}}
        <section class="space-y-3">
            @if (session('success'))
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('status') === 'application-already-reviewed')
                <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-5 py-4 text-yellow-100">
                    Đơn này đã được xử lý trước đó.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    <div class="font-bold mb-2">Có lỗi xảy ra:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- PROFILE OVERVIEW --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="text-sm text-slate-500">Headline</div>
                <div class="mt-2 text-lg font-bold text-white text-left break-words [overflow-wrap:anywhere]">
                    {{ $application->headline ?: 'Chưa có headline' }}
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="text-sm text-slate-500">Website</div>
                <div class="mt-2 text-sm text-cyan-200 text-left break-words [overflow-wrap:anywhere]">
                    @if ($application->website_url)
                        <a href="{{ $application->website_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-100">
                            {{ $application->website_url }}
                        </a>
                    @else
                        <span class="text-slate-400">Chưa có</span>
                    @endif
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="text-sm text-slate-500">LinkedIn / X</div>
                <div class="mt-2 space-y-1 text-sm text-left break-words [overflow-wrap:anywhere]">
                    @if ($application->linkedin_url)
                        <a href="{{ $application->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="block text-cyan-200 hover:text-cyan-100">
                            {{ $application->linkedin_url }}
                        </a>
                    @endif

                    @if ($application->x_url)
                        <a href="{{ $application->x_url }}" target="_blank" rel="noopener noreferrer" class="block text-cyan-200 hover:text-cyan-100">
                            {{ $application->x_url }}
                        </a>
                    @endif

                    @if (! $application->linkedin_url && ! $application->x_url)
                        <span class="text-slate-400">Chưa có</span>
                    @endif
                </div>
            </div>
        </section>

        {{-- EXPERTISE --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
            <div class="p-5 md:p-6 border-b border-white/10">
                <h2 class="text-2xl font-black text-white">
                    Lĩnh vực chuyên môn
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Các mảng nội dung người dùng tự đánh giá có kinh nghiệm hoặc quan tâm.
                </p>
            </div>

            <div class="p-5 md:p-6">
                @if (! empty($areas))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($areas as $area)
                            <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm font-semibold text-cyan-100">
                                {{ $area }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4 text-sm text-slate-400">
                        Người dùng chưa chọn lĩnh vực chuyên môn.
                    </div>
                @endif
            </div>
        </section>

        {{-- MAIN CONTENT --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
                <div class="p-5 md:p-6 border-b border-white/10">
                    <h2 class="text-2xl font-black text-white">
                        Tóm tắt kinh nghiệm
                    </h2>
                </div>

                <div class="p-5 md:p-6">
                    <div class="admin-review-content rounded-3xl border border-white/10 bg-slate-950/60 p-5 text-left text-slate-200 leading-8 whitespace-pre-line break-words [overflow-wrap:anywhere]">
                        {{ $application->experience_summary ?: 'Chưa có nội dung.' }}
                    </div>
                </div>
            </article>

            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
                <div class="p-5 md:p-6 border-b border-white/10">
                    <h2 class="text-2xl font-black text-white">
                        Lý do muốn trở thành tác giả
                    </h2>
                </div>

                <div class="p-5 md:p-6">
                    <div class="admin-review-content rounded-3xl border border-white/10 bg-slate-950/60 p-5 text-left text-slate-200 leading-8 whitespace-pre-line break-words [overflow-wrap:anywhere]">
                        {{ $application->motivation ?: 'Chưa có nội dung.' }}
                    </div>
                </div>
            </article>
        </section>

        {{-- SAMPLE ARTICLE --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
            <div class="p-5 md:p-6 border-b border-white/10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-white">
                            Bài viết mẫu
                        </h2>

                        <p class="mt-2 text-sm text-slate-400">
                            Admin nên đánh giá độ rõ ràng, tính chuyên môn, tính trung lập và rủi ro nội dung.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                        Sample content
                    </div>
                </div>
            </div>

            <div class="p-5 md:p-6">
                <div class="rounded-3xl border border-white/10 bg-slate-950/60 p-5 md:p-7">
                    <h3 class="text-left text-2xl md:text-3xl font-black text-white leading-tight break-words [overflow-wrap:anywhere]">
                        {{ $application->sample_article_title ?: 'Chưa có tiêu đề bài viết mẫu' }}
                    </h3>

                    <div class="admin-review-content mt-6 text-left text-slate-200 leading-8 whitespace-pre-line break-words [overflow-wrap:anywhere]">
                        {{ $application->sample_article_content ?: 'Chưa có nội dung bài viết mẫu.' }}
                    </div>
                </div>
            </div>
        </section>

        {{-- REVIEW HISTORY --}}
        @if ($application->isApproved() || $application->isRejected())
            <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-5 md:p-6">
                <h2 class="text-2xl font-black text-white">
                    Kết quả xét duyệt
                </h2>

                <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-sm text-slate-500">Trạng thái</div>
                        <div class="mt-1 font-bold text-white">{{ $badge['label'] }}</div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-sm text-slate-500">Người duyệt</div>
                        <div class="mt-1 font-bold text-white">
                            {{ $application->reviewer?->name ?? 'Không rõ' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-sm text-slate-500">Thời gian duyệt</div>
                        <div class="mt-1 font-bold text-white">
                            {{ $formatDate($application->reviewed_at) }}
                        </div>
                    </div>
                </div>

                @if ($application->isRejected() && $application->rejection_reason)
                    <div class="admin-review-content mt-5 rounded-3xl border border-rose-400/20 bg-rose-400/10 p-5 text-left text-rose-100 leading-8 whitespace-pre-line break-words [overflow-wrap:anywhere]">
                        {{ $application->rejection_reason }}
                    </div>
                @endif
            </section>
        @endif

        {{-- ACTIONS --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
            <div class="p-5 md:p-6 border-b border-white/10">
                <h2 class="text-2xl font-black text-white">
                    Hành động kiểm duyệt
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Nếu hồ sơ phù hợp, admin có thể duyệt để cấp quyền AUTHOR. Nếu chưa phù hợp, hãy từ chối kèm lý do rõ ràng.
                </p>
            </div>

            <div class="p-5 md:p-6">
                @if ($application->isPending())
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <form method="POST"
                              action="{{ route('admin.author-applications.approve', $application) }}"
                              onsubmit="return confirm('Bạn có chắc muốn duyệt đơn này và cấp quyền AUTHOR cho người dùng không?')"
                              class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                            @csrf
                            @method('PATCH')

                            <div class="text-lg font-black text-emerald-100">
                                Duyệt đơn
                            </div>

                            <p class="mt-2 text-sm text-slate-300 leading-7 text-left">
                                Người dùng sẽ được cấp quyền AUTHOR và có thể viết bài trên hệ thống.
                            </p>

                            <button type="submit"
                                    class="mt-5 w-full rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                                Duyệt và cấp quyền AUTHOR
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.author-applications.reject', $application) }}"
                              onsubmit="return confirm('Bạn có chắc muốn từ chối đơn này không?')"
                              class="rounded-3xl border border-rose-400/20 bg-rose-400/10 p-5">
                            @csrf
                            @method('PATCH')

                            <div class="text-lg font-black text-rose-100">
                                Từ chối đơn
                            </div>

                            <p class="mt-2 text-sm text-slate-300 leading-7 text-left">
                                Lý do từ chối sẽ giúp người dùng hiểu cần bổ sung hoặc chỉnh sửa gì.
                            </p>

                            <label class="mt-4 block text-sm font-bold text-slate-200 mb-2">
                                Lý do từ chối
                            </label>

                            <textarea name="rejection_reason"
                                      rows="5"
                                      required
                                      class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-rose-400 focus:ring-rose-400/30"
                                      placeholder="VD: Hồ sơ cần bổ sung kinh nghiệm cụ thể hơn, bài viết mẫu còn ngắn hoặc chưa thể hiện đủ kiến thức crypto...">{{ old('rejection_reason') }}</textarea>

                            <button type="submit"
                                    class="mt-5 w-full rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 hover:from-rose-400 hover:to-orange-400 transition">
                                Từ chối đơn
                            </button>
                        </form>
                    </div>
                @else
                    <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                        <div class="text-lg font-bold text-white">
                            Đơn này đã được xử lý.
                        </div>

                        <p class="mt-2 text-sm text-slate-400 leading-7 text-left">
                            Bạn có thể quay lại danh sách để xem các hồ sơ khác.
                        </p>

                        <a href="{{ route('admin.author-applications.index') }}"
                           class="mt-5 inline-flex rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Quay lại danh sách
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-guest-layout>