<x-guest-layout>
    @php
        $comments = $comments ?? collect();
        $type = $type ?? request('type', 'all');
        $search = $search ?? request('search');

        $typeConfig = [
            'all' => [
                'label' => 'Tất cả',
                'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
                'dot' => 'bg-slate-300',
            ],
            'blog' => [
                'label' => 'Blog',
                'class' => 'bg-indigo-400/10 text-indigo-200 border-indigo-400/20',
                'dot' => 'bg-indigo-300',
            ],
            'news' => [
                'label' => 'News',
                'class' => 'bg-blue-400/10 text-blue-200 border-blue-400/20',
                'dot' => 'bg-blue-300',
            ],
        ];

        $commentCount = method_exists($comments, 'total') ? $comments->total() : $comments->count();

        $commentType = function ($comment) {
            if (! empty($comment->blog_post_id)) {
                return 'blog';
            }

            if (! empty($comment->news_id)) {
                return 'news';
            }

            return 'unknown';
        };

        $commentTypeBadge = function ($comment) {
            if (! empty($comment->blog_post_id)) {
                return [
                    'label' => 'BLOG',
                    'class' => 'bg-indigo-400/10 text-indigo-200 border-indigo-400/20',
                    'dot' => 'bg-indigo-300',
                ];
            }

            if (! empty($comment->news_id)) {
                return [
                    'label' => 'NEWS',
                    'class' => 'bg-blue-400/10 text-blue-200 border-blue-400/20',
                    'dot' => 'bg-blue-300',
                ];
            }

            return [
                'label' => 'UNKNOWN',
                'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
                'dot' => 'bg-slate-300',
            ];
        };
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-blue-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-sm text-blue-200">
                        <span class="h-2 w-2 rounded-full bg-blue-300 shadow-[0_0_14px_rgba(147,197,253,0.9)]"></span>
                        Community moderation
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Quản lý
                        <span class="bg-gradient-to-r from-blue-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                            bình luận
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Theo dõi và xử lý các bình luận trong Blog và Tin tức, giúp cộng đồng thảo luận an toàn, hạn chế spam, scam và nội dung không phù hợp.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            ← Dashboard
                        </a>

                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                            Xem Blog
                        </a>

                        <a href="{{ route('news.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Xem News
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Bình luận đang hiển thị</div>
                                <div class="mt-1 text-4xl font-black text-white">{{ number_format($commentCount) }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 6.5C5 5.11929 6.11929 4 7.5 4H16.5C17.8807 4 19 5.11929 19 6.5V13.5C19 14.8807 17.8807 16 16.5 16H11L6 20V16.5C5.40326 16.1531 5 15.5077 5 14.75V6.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M8.5 8.5H15.5M8.5 11.5H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-indigo-400/20 bg-indigo-400/10 p-4">
                                <div class="text-xs text-indigo-200">Blog comments</div>
                                <div class="mt-1 text-2xl font-black text-white">{{ number_format($blogComments ?? 0) }}</div>
                            </div>

                            <div class="rounded-2xl border border-blue-400/20 bg-blue-400/10 p-4">
                                <div class="text-xs text-blue-200">News comments</div>
                                <div class="mt-1 text-2xl font-black text-white">{{ number_format($newsComments ?? 0) }}</div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4">
                            <div class="text-sm font-semibold text-yellow-200">
                                Gợi ý kiểm duyệt
                            </div>

                            <p class="mt-1 text-sm text-slate-300">
                                Xóa bình luận chứa spam, link scam, lời mời đầu tư không rõ ràng hoặc nội dung công kích người dùng khác.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FLASH --}}
        @if (session('success'))
            <section class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                {{ session('success') }}
            </section>
        @endif

        {{-- FILTER --}}
        <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 md:p-6">
            <form method="GET" action="{{ route('admin.comments.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                <div class="lg:col-span-6">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Tìm kiếm bình luận
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Tìm theo nội dung, tên user, email, tiêu đề blog/news..."
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Loại bình luận
                    </label>

                    <select name="type"
                            class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                        <option value="all" @selected($type === 'all')>Tất cả comment</option>
                        <option value="blog" @selected($type === 'blog')>Chỉ Blog</option>
                        <option value="news" @selected($type === 'news')>Chỉ News</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-end gap-2">
                    <button type="submit"
                            class="w-full rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                        Lọc
                    </button>

                    <a href="{{ route('admin.comments.index') }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/10 transition">
                        Xóa
                    </a>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap gap-2">
                @foreach (['all', 'blog', 'news'] as $tab)
                    @php
                        $config = $typeConfig[$tab];
                        $active = $type === $tab;
                    @endphp

                    <a href="{{ route('admin.comments.index', array_filter(['type' => $tab, 'search' => $search])) }}"
                       class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition
                            {{ $active ? $config['class'] : 'border-white/10 bg-slate-950/40 text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <span class="h-2 w-2 rounded-full {{ $active ? $config['dot'] : 'bg-slate-500' }}"></span>
                        {{ $config['label'] }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- COMMENTS LIST --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 md:p-6 border-b border-white/10">
                <div>
                    <h2 class="text-2xl font-black text-white">
                        Danh sách bình luận
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Xem nội dung, người gửi, bài viết liên quan và xử lý bình luận vi phạm.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                    Tổng: <span class="font-bold text-white">{{ number_format($commentCount) }}</span>
                </div>
            </div>

            @if ($comments->count() === 0)
                <div class="p-10 text-center">
                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                        <span class="text-3xl">💬</span>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-white">
                        Không có bình luận phù hợp
                    </h3>

                    <p class="mt-2 text-slate-400">
                        Hãy thử đổi bộ lọc hoặc từ khóa tìm kiếm.
                    </p>

                    <a href="{{ route('admin.comments.index') }}"
                       class="inline-flex mt-5 rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white">
                        Xem tất cả bình luận
                    </a>
                </div>
            @else
                <div class="divide-y divide-white/10">
                    @foreach ($comments as $comment)
                        @php
                            $badge = $commentTypeBadge($comment);
                            $relatedTitle = null;
                            $relatedUrl = null;

                            if ($comment->blogPost) {
                                $relatedTitle = $comment->blogPost->title;
                                if (! empty($comment->blogPost->slug) && ($comment->blogPost->status ?? 'approved') === 'approved') {
                                    $relatedUrl = route('blog.show', $comment->blogPost->slug);
                                }
                            } elseif ($comment->news) {
                                $relatedTitle = $comment->news->title;
                                if (! empty($comment->news->slug)) {
                                    $relatedUrl = route('news.show', $comment->news->slug);
                                }
                            }
                        @endphp

                        <article class="p-5 md:p-6 hover:bg-white/[0.025] transition">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                {{-- Main content --}}
                                <div class="lg:col-span-8 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $badge['class'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                                            {{ $badge['label'] }}
                                        </span>

                                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                            {{ $comment->created_at ? $comment->created_at->format('d/m/Y H:i') : 'Chưa rõ thời gian' }}
                                        </span>
                                    </div>

                                    <div class="mt-4 flex items-start gap-3">
                                        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-sm font-black text-white shrink-0">
                                            {{ strtoupper(mb_substr($comment->user?->name ?? 'U', 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-white break-words [overflow-wrap:anywhere]">
                                                {{ $comment->user?->name ?? 'Người dùng đã xóa' }}
                                            </div>

                                            <div class="mt-1 text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                                {{ $comment->user?->email ?? 'Không có email' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 rounded-3xl border border-white/10 bg-slate-950/60 p-5">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Nội dung bình luận
                                        </div>

                                        <p class="mt-3 text-slate-200 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-7">
                                            {{ $comment->content }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Sidebar --}}
                                <div class="lg:col-span-4">
                                    <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 space-y-4">
                                        <div>
                                            <div class="text-xs text-slate-500">
                                                Nội dung liên quan
                                            </div>

                                            <div class="mt-2 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                                                {{ $relatedTitle ?? 'Bài viết/tin tức đã bị xóa hoặc không tồn tại' }}
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            @if ($relatedUrl)
                                                <a href="{{ $relatedUrl }}"
                                                   class="inline-flex items-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-3 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                                    Xem nội dung →
                                                </a>
                                            @endif

                                            <form method="POST"
                                                  action="{{ route('admin.comments.destroy', $comment) }}"
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa bình luận này không?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex items-center rounded-xl border border-rose-400/20 bg-rose-500/15 px-3 py-2 text-sm font-semibold text-rose-100 hover:bg-rose-500/25 transition">
                                                    Xóa comment
                                                </button>
                                            </form>
                                        </div>

                                        <div class="rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4 text-sm text-yellow-100">
                                            Xóa comment nếu có spam, link lừa đảo, xúc phạm hoặc nội dung không phù hợp với cộng đồng.
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