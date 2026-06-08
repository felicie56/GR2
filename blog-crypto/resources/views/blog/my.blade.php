<x-guest-layout>
    @php
        $posts = $posts ?? collect();
        $postCount = method_exists($posts, 'total') ? $posts->total() : $posts->count();

        $statusConfig = [
            'pending' => [
                'label' => 'Chờ duyệt',
                'class' => 'bg-yellow-400/10 text-yellow-200 border-yellow-400/20',
                'dot' => 'bg-yellow-300',
                'message' => 'Bài viết đang chờ admin kiểm duyệt trước khi public.',
            ],
            'approved' => [
                'label' => 'Đã duyệt',
                'class' => 'bg-emerald-400/10 text-emerald-200 border-emerald-400/20',
                'dot' => 'bg-emerald-300',
                'message' => 'Bài viết đã được công khai trên trang Blog.',
            ],
            'rejected' => [
                'label' => 'Bị từ chối',
                'class' => 'bg-rose-400/10 text-rose-200 border-rose-400/20',
                'dot' => 'bg-rose-300',
                'message' => 'Bạn có thể chỉnh sửa bài theo góp ý và gửi duyệt lại.',
            ],
        ];

        $statusBadge = function ($status) use ($statusConfig) {
            return $statusConfig[$status] ?? [
                'label' => strtoupper((string) $status),
                'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
                'dot' => 'bg-slate-300',
                'message' => 'Trạng thái bài viết chưa xác định.',
            ];
        };
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-cyan-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm text-cyan-200">
                        <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_14px_rgba(103,232,249,0.9)]"></span>
                        Author workspace
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Bài viết
                        <span class="bg-gradient-to-r from-cyan-300 via-indigo-300 to-emerald-300 bg-clip-text text-transparent">
                            của tôi
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Theo dõi trạng thái duyệt bài, chỉnh sửa nội dung và gửi lại các bài bị từ chối để admin kiểm duyệt.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('blog.create') }}"
                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                            + Viết bài mới
                        </a>

                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Xem Blog public
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Tổng bài của bạn</div>
                                <div class="mt-1 text-4xl font-black text-white">{{ number_format($postCount) }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                                ✍️
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4">
                            <div class="text-sm font-semibold text-yellow-200">
                                Quy trình public
                            </div>
                            <p class="mt-1 text-sm text-slate-300">
                                Bài viết sau khi tạo hoặc chỉnh sửa sẽ chuyển về trạng thái chờ duyệt.
                            </p>
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

            @if (session('warning'))
                <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-5 py-4 text-yellow-100">
                    {{ session('warning') }}
                </div>
            @endif
        </section>

        {{-- LIST --}}
        @if ($posts->count() === 0)
            <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-10 text-center">
                <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                    <span class="text-3xl">📝</span>
                </div>

                <h2 class="mt-5 text-2xl font-black text-white">
                    Bạn chưa có bài viết nào
                </h2>

                <p class="mt-2 text-slate-400">
                    Hãy tạo bài viết đầu tiên để gửi admin kiểm duyệt.
                </p>

                <a href="{{ route('blog.create') }}"
                   class="inline-flex mt-6 rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white">
                    Viết bài mới
                </a>
            </section>
        @else
            <section class="space-y-5">
                @foreach ($posts as $post)
                    @php
                        $badge = $statusBadge($post->status);
                    @endphp

                    <article class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            <div class="lg:col-span-4 min-h-[240px] bg-slate-950/60 border-b lg:border-b-0 lg:border-r border-white/10">
                                @if ($post->thumbnail)
                                    <img src="{{ $post->thumbnail }}"
                                         alt="{{ $post->title }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <div class="h-full min-h-[240px] bg-gradient-to-br from-slate-800 via-indigo-950 to-cyan-950 flex items-center justify-center">
                                        <div class="h-20 w-20 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl font-black text-white">
                                            {{ strtoupper(mb_substr($post->title, 0, 1)) }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="lg:col-span-8 p-5 md:p-6">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $badge['class'] }}">
                                                <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                                                {{ $badge['label'] }}
                                            </span>

                                            <span class="rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-xs font-semibold text-indigo-200">
                                                {{ $post->category?->name ?? 'Chưa phân loại' }}
                                            </span>

                                            <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                                {{ $post->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>

                                        <h2 class="mt-4 text-2xl md:text-3xl font-black text-white leading-tight break-words [overflow-wrap:anywhere]">
                                            {{ $post->title }}
                                        </h2>

                                        <p class="mt-3 text-sm text-slate-400 leading-7 break-words [overflow-wrap:anywhere]">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 220) }}
                                        </p>

                                        <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                                            <div class="text-sm font-semibold text-slate-200">
                                                Trạng thái xử lý
                                            </div>

                                            <p class="mt-1 text-sm text-slate-400">
                                                {{ $badge['message'] }}
                                            </p>

                                            @if ($post->reviewed_at)
                                                <p class="mt-2 text-xs text-slate-500">
                                                    Xử lý lúc: {{ $post->reviewed_at->format('d/m/Y H:i') }}
                                                </p>
                                            @endif
                                        </div>

                                        @if ($post->status === 'rejected' && $post->rejection_reason)
                                            <div class="mt-4 rounded-2xl bg-rose-400/10 border border-rose-400/20 p-4 text-rose-100">
                                                <div class="font-semibold text-sm">
                                                    Lý do từ chối:
                                                </div>

                                                <p class="mt-2 text-sm whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-7">
                                                    {{ $post->rejection_reason }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="shrink-0 flex flex-wrap lg:flex-col gap-3">
                                        @if ($post->status === 'approved')
                                            <a href="{{ route('blog.show', $post->slug) }}"
                                               class="inline-flex justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                                Xem public
                                            </a>
                                        @endif

                                        <a href="{{ route('blog.edit', $post) }}"
                                           class="inline-flex justify-center rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                                            Sửa bài
                                        </a>

                                        <form method="POST"
                                              action="{{ route('blog.destroy', $post) }}"
                                              onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này không?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="w-full rounded-2xl border border-rose-400/20 bg-rose-500/15 px-4 py-2 text-sm font-semibold text-rose-100 hover:bg-rose-500/25 transition">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-guest-layout>