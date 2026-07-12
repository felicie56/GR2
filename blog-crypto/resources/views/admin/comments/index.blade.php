<x-guest-layout>
    @php
        $comments = $comments ?? collect();
        $type = $type ?? request('type', 'all');
        $status = $status ?? request('status', 'pending');
        $search = $search ?? request('search');

        $statusConfig = [
            'pending' => [
                'label' => 'Chờ duyệt',
                'class' => 'border-amber-400/20 bg-amber-400/10 text-amber-200',
                'dot' => 'bg-amber-300',
            ],
            'approved' => [
                'label' => 'Đã duyệt',
                'class' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200',
                'dot' => 'bg-emerald-300',
            ],
            'rejected' => [
                'label' => 'Từ chối',
                'class' => 'border-rose-400/20 bg-rose-400/10 text-rose-200',
                'dot' => 'bg-rose-300',
            ],
        ];

        $typeConfig = [
            'blog' => [
                'label' => 'BLOG',
                'class' => 'border-indigo-400/20 bg-indigo-400/10 text-indigo-200',
            ],
            'news' => [
                'label' => 'NEWS',
                'class' => 'border-blue-400/20 bg-blue-400/10 text-blue-200',
            ],
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-blue-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
            </div>

            <div class="relative p-6 md:p-10">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-sm text-amber-200">
                            <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                            Comment moderation
                        </div>

                        <h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-white">
                            Kiểm duyệt bình luận
                        </h1>

                        <p class="mt-4 max-w-2xl text-slate-300 leading-relaxed">
                            Bình luận mới sẽ ở trạng thái chờ duyệt.
                            Chỉ bình luận được admin duyệt mới xuất hiện trên Blog hoặc Tin tức.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition"
                        >
                            ← Dashboard
                        </a>

                        <a
                            href="{{ route('blog.index') }}"
                            class="rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white hover:from-blue-400 hover:to-cyan-400 transition"
                        >
                            Xem Blog
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-5 py-4 text-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a
                href="{{ route('admin.comments.index', [
                    'status' => 'all',
                    'type' => $type,
                    'search' => $search,
                ]) }}"
                class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 hover:bg-white/[0.07] transition"
            >
                <div class="text-sm text-slate-400">
                    Tất cả
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ number_format($totalComments ?? 0) }}
                </div>
            </a>

            <a
                href="{{ route('admin.comments.index', [
                    'status' => 'pending',
                    'type' => $type,
                    'search' => $search,
                ]) }}"
                class="rounded-3xl border border-amber-400/20 bg-amber-400/10 p-5 hover:bg-amber-400/15 transition"
            >
                <div class="text-sm text-amber-200">
                    Chờ duyệt
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ number_format($pendingComments ?? 0) }}
                </div>
            </a>

            <a
                href="{{ route('admin.comments.index', [
                    'status' => 'approved',
                    'type' => $type,
                    'search' => $search,
                ]) }}"
                class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5 hover:bg-emerald-400/15 transition"
            >
                <div class="text-sm text-emerald-200">
                    Đã duyệt
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ number_format($approvedComments ?? 0) }}
                </div>
            </a>

            <a
                href="{{ route('admin.comments.index', [
                    'status' => 'rejected',
                    'type' => $type,
                    'search' => $search,
                ]) }}"
                class="rounded-3xl border border-rose-400/20 bg-rose-400/10 p-5 hover:bg-rose-400/15 transition"
            >
                <div class="text-sm text-rose-200">
                    Từ chối
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ number_format($rejectedComments ?? 0) }}
                </div>
            </a>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-5 md:p-6">
            <form
                method="GET"
                action="{{ route('admin.comments.index') }}"
                class="grid grid-cols-1 md:grid-cols-12 gap-4"
            >
                <div class="md:col-span-5">
                    <label
                        for="search"
                        class="block text-sm font-semibold text-slate-300 mb-2"
                    >
                        Tìm kiếm
                    </label>

                    <input
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nội dung, người gửi hoặc tiêu đề..."
                        class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400/30"
                    >
                </div>

                <div class="md:col-span-3">
                    <label
                        for="status"
                        class="block text-sm font-semibold text-slate-300 mb-2"
                    >
                        Trạng thái
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400/30"
                    >
                        <option
                            value="all"
                            @selected($status === 'all')
                        >
                            Tất cả trạng thái
                        </option>

                        <option
                            value="pending"
                            @selected($status === 'pending')
                        >
                            Chờ duyệt
                        </option>

                        <option
                            value="approved"
                            @selected($status === 'approved')
                        >
                            Đã duyệt
                        </option>

                        <option
                            value="rejected"
                            @selected($status === 'rejected')
                        >
                            Từ chối
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label
                        for="type"
                        class="block text-sm font-semibold text-slate-300 mb-2"
                    >
                        Loại
                    </label>

                    <select
                        id="type"
                        name="type"
                        class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400/30"
                    >
                        <option
                            value="all"
                            @selected($type === 'all')
                        >
                            Tất cả
                        </option>

                        <option
                            value="blog"
                            @selected($type === 'blog')
                        >
                            Blog
                        </option>

                        <option
                            value="news"
                            @selected($type === 'news')
                        >
                            News
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-end gap-2">
                    <button
                        type="submit"
                        class="flex-1 rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-3 text-sm font-bold text-white hover:from-blue-400 hover:to-cyan-400 transition"
                    >
                        Lọc
                    </button>

                    <a
                        href="{{ route('admin.comments.index') }}"
                        class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/10 transition"
                    >
                        Xóa
                    </a>
                </div>
            </form>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="p-5 md:p-6 border-b border-white/10">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-white">
                            Danh sách bình luận
                        </h2>

                        <p class="mt-1 text-sm text-slate-400">
                            Kiểm tra nội dung trước khi cho phép hiển thị công khai.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                        Kết quả:
                        <span class="font-bold text-white">
                            {{ number_format($comments->total()) }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($comments->count() === 0)
                <div class="p-10 text-center">
                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center text-3xl">
                        💬
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-white">
                        Không có bình luận phù hợp
                    </h3>

                    <p class="mt-2 text-slate-400">
                        Hãy thử thay đổi trạng thái, loại hoặc từ khóa tìm kiếm.
                    </p>
                </div>
            @else
                <div class="divide-y divide-white/10">
                    @foreach ($comments as $comment)
                        @php
                            $commentStatus = $statusConfig[$comment->status] ?? [
                                'label' => strtoupper($comment->status ?? 'UNKNOWN'),
                                'class' => 'border-slate-400/20 bg-slate-400/10 text-slate-200',
                                'dot' => 'bg-slate-300',
                            ];

                            $commentType = $comment->blog_post_id
                                ? 'blog'
                                : 'news';

                            $commentTypeBadge = $typeConfig[$commentType];

                            $relatedTitle =
                                $comment->blogPost?->title
                                ?? $comment->news?->title;

                            $relatedUrl = null;

                            if (
                                $comment->blogPost?->slug
                                && strtolower((string) $comment->blogPost->status) === 'approved'
                            ) {
                                $relatedUrl = route(
                                    'blog.show',
                                    $comment->blogPost->slug
                                );
                            } elseif ($comment->news?->slug) {
                                $relatedUrl = route(
                                    'news.show',
                                    $comment->news->slug
                                );
                            }
                        @endphp

                        <article class="p-5 md:p-6 hover:bg-white/[0.025] transition">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <div class="lg:col-span-8 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $commentStatus['class'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $commentStatus['dot'] }}"></span>
                                            {{ $commentStatus['label'] }}
                                        </span>

                                        <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $commentTypeBadge['class'] }}">
                                            {{ $commentTypeBadge['label'] }}
                                        </span>

                                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                            {{ $comment->created_at?->format('d/m/Y H:i')
                                                ?? 'Chưa rõ thời gian' }}
                                        </span>
                                    </div>

                                    <div class="mt-4 flex items-start gap-3">
                                        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-sm font-black text-white shrink-0">
                                            {{ strtoupper(mb_substr(
                                                $comment->user?->name ?? 'U',
                                                0,
                                                1
                                            )) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-white break-words [overflow-wrap:anywhere]">
                                                {{ $comment->user?->name
                                                    ?? 'Người dùng đã xóa' }}
                                            </div>

                                            <div class="mt-1 text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                                {{ $comment->user?->email
                                                    ?? 'Không có email' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 rounded-3xl border border-white/10 bg-slate-950/60 p-5">
                                        <p class="text-slate-200 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-7">
                                            {{ $comment->content }}
                                        </p>
                                    </div>
                                </div>

                                <div class="lg:col-span-4">
                                    <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 space-y-5">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                                Nội dung liên quan
                                            </div>

                                            <div class="mt-2 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                                                {{ $relatedTitle
                                                    ?? 'Nội dung không còn tồn tại' }}
                                            </div>

                                            @if ($relatedUrl)
                                                <a
                                                    href="{{ $relatedUrl }}"
                                                    class="inline-flex mt-3 text-sm font-semibold text-cyan-300 hover:text-cyan-200"
                                                >
                                                    Xem nội dung →
                                                </a>
                                            @endif
                                        </div>

                                        @if ($comment->reviewed_at)
                                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-sm text-slate-400">
                                                Xử lý bởi
                                                <span class="font-semibold text-slate-200">
                                                    {{ $comment->reviewer?->name ?? 'Admin' }}
                                                </span>
                                                <br>

                                                lúc
                                                {{ $comment->reviewed_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endif

                                        <div class="flex flex-wrap gap-2">
                                            @if (! $comment->isApproved())
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.comments.approve', $comment) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="rounded-xl border border-emerald-400/20 bg-emerald-500/15 px-3 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-500/25 transition"
                                                    >
                                                        Duyệt
                                                    </button>
                                                </form>
                                            @endif

                                            @if (! $comment->isRejected())
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.comments.reject', $comment) }}"
                                                    onsubmit="return confirm('Bạn có chắc muốn từ chối bình luận này không?')"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="rounded-xl border border-amber-400/20 bg-amber-500/15 px-3 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/25 transition"
                                                    >
                                                        Từ chối
                                                    </button>
                                                </form>
                                            @endif

                                            <form
                                                method="POST"
                                                action="{{ route('admin.comments.destroy', $comment) }}"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn bình luận này không?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-xl border border-rose-400/20 bg-rose-500/15 px-3 py-2 text-sm font-semibold text-rose-100 hover:bg-rose-500/25 transition"
                                                >
                                                    Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="p-5 border-t border-white/10">
                    {{ $comments->links() }}
                </div>
            @endif
        </section>
    </div>
</x-guest-layout>