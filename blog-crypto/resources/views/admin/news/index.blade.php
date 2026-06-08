<x-guest-layout>
    @php
        $newsItems = $news ?? $articles ?? collect();
        $categoryList = $categories ?? collect();
        $search = $search ?? request('search');
        $categoryId = $categoryId ?? request('category');

        $isPaginator = is_object($newsItems) && method_exists($newsItems, 'total');
        $newsCount = $isPaginator ? $newsItems->total() : $newsItems->count();

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
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-blue-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-sm text-blue-200">
                        <span class="h-2 w-2 rounded-full bg-blue-300 shadow-[0_0_14px_rgba(147,197,253,0.9)]"></span>
                        News CMS
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Quản lý
                        <span class="bg-gradient-to-r from-blue-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                            tin tức
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Tạo, chỉnh sửa, xóa và kiểm soát các tin tức crypto/tài chính hiển thị trên website.
                        Tin tức có thể được nhập thủ công bởi admin hoặc tự động tổng hợp từ RSS.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            ← Dashboard
                        </a>

                        <a href="{{ route('admin.news.create') }}"
                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                            + Tạo tin tức mới
                        </a>

                        <a href="{{ route('news.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Xem trang News
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Tin tức đang quản lý</div>
                                <div class="mt-1 text-4xl font-black text-white">{{ number_format($newsCount) }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 4H16L19 7V20H5V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M8 9H16M8 12H16M8 15H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                            <div class="text-sm font-semibold text-emerald-200">
                                Auto RSS
                            </div>

                            <p class="mt-1 text-sm text-slate-300">
                                Tin tự động sẽ có badge riêng, có nguồn gốc rõ ràng và vẫn được gán category để user lọc/tìm kiếm chính xác.
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

        {{-- FILTER --}}
        <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 md:p-6">
            <form method="GET" action="{{ route('admin.news.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                <div class="lg:col-span-6">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Tìm kiếm tin tức
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Tìm theo tiêu đề, nội dung, tóm tắt, nguồn tin hoặc source URL..."
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Chuyên mục
                    </label>

                    <select name="category"
                            class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                        <option value="">Tất cả chuyên mục</option>
                        @foreach ($categoryList as $category)
                            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-end gap-2">
                    <button type="submit"
                            class="w-full rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                        Lọc
                    </button>

                    <a href="{{ route('admin.news.index') }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/10 transition">
                        Xóa
                    </a>
                </div>
            </form>
        </section>

        {{-- LIST --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 md:p-6 border-b border-white/10">
                <div>
                    <h2 class="text-2xl font-black text-white">
                        Danh sách tin tức
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Quản lý tin nhập thủ công và tin tự động từ RSS. Admin vẫn có thể sửa/xóa tin tự động nếu cần.
                    </p>
                </div>

                <a href="{{ route('admin.news.create') }}"
                   class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                    + Tạo tin mới
                </a>
            </div>

            @if ($newsItems->count() === 0)
                <div class="p-10 text-center">
                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                        <span class="text-3xl">📰</span>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-white">
                        Chưa có tin tức nào
                    </h3>

                    <p class="mt-2 text-slate-400">
                        Hãy tạo tin tức thủ công hoặc chạy command tự động lấy tin RSS.
                    </p>

                    <div class="mt-5 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('admin.news.create') }}"
                           class="inline-flex rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white">
                            Tạo tin tức mới
                        </a>

                        <div class="inline-flex rounded-2xl border border-white/10 bg-slate-950/60 px-5 py-3 text-sm font-semibold text-slate-300">
                            php artisan news:fetch-rss --limit=5
                        </div>
                    </div>
                </div>
            @else
                <div class="divide-y divide-white/10">
                    @foreach ($newsItems as $article)
                        <article class="p-5 md:p-6 hover:bg-white/[0.025] transition">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                {{-- Thumbnail --}}
                                <div class="lg:col-span-3">
                                    <div class="h-48 lg:h-full min-h-[180px] rounded-3xl overflow-hidden border border-white/10 bg-slate-950/60">
                                        @if ($article->thumbnail)
                                            <img src="{{ $article->thumbnail }}"
                                                 alt="{{ $article->title }}"
                                                 class="h-full w-full object-cover">
                                        @else
                                            <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-900 to-cyan-950 flex items-center justify-center">
                                                <div class="h-16 w-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl font-black text-white">
                                                    {{ strtoupper(mb_substr($article->title, 0, 1)) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="lg:col-span-6 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-xs font-bold text-blue-200">
                                            {{ $article->category?->name ?? 'Chưa phân loại' }}
                                        </span>

                                        @if ($article->is_auto)
                                            <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-bold text-emerald-200">
                                                Auto RSS
                                            </span>
                                        @else
                                            <span class="rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-xs font-bold text-indigo-200">
                                                Manual
                                            </span>
                                        @endif

                                        @if ($article->source)
                                            <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400 break-words [overflow-wrap:anywhere]">
                                                {{ $article->source }}
                                            </span>
                                        @endif

                                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                            {{ $formatDate($article->published_at ?: $article->created_at) }}
                                        </span>
                                    </div>

                                    <h3 class="mt-4 text-2xl font-black text-white leading-tight break-words [overflow-wrap:anywhere]">
                                        {{ $article->title }}
                                    </h3>

                                    <p class="mt-3 text-sm text-slate-400 leading-7 break-words [overflow-wrap:anywhere]">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->summary ?: $article->content), 230) }}
                                    </p>

                                    @if ($article->source_url)
                                        <div class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                                            <div class="text-xs font-bold text-emerald-200">
                                                Nguồn gốc
                                            </div>

                                            <a href="{{ $article->source_url }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="mt-1 block text-sm text-emerald-100 hover:text-white break-all">
                                                {{ $article->source_url }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="lg:col-span-3">
                                    <div class="h-full rounded-3xl border border-white/10 bg-slate-950/50 p-5 flex flex-col justify-between gap-4">
                                        <div class="space-y-3">
                                            @if (! empty($article->slug))
                                                <a href="{{ route('news.show', $article->slug) }}"
                                                   class="block rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-center text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                                    Xem public →
                                                </a>
                                            @endif

                                            @if ($article->source_url)
                                                <a href="{{ $article->source_url }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="block rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-center text-sm font-semibold text-emerald-200 hover:bg-emerald-400/15 transition">
                                                    Đọc nguồn gốc →
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.news.edit', $article->id) }}"
                                               class="block rounded-xl border border-indigo-400/20 bg-indigo-400/10 px-4 py-2 text-center text-sm font-semibold text-indigo-200 hover:bg-indigo-400/15 transition">
                                                Chỉnh sửa
                                            </a>
                                        </div>

                                        <form method="POST"
                                              action="{{ route('admin.news.destroy', $article->id) }}"
                                              onsubmit="return confirm('Bạn có chắc muốn xóa tin tức này không?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="w-full rounded-xl border border-rose-400/20 bg-rose-500/15 px-4 py-2 text-sm font-semibold text-rose-100 hover:bg-rose-500/25 transition">
                                                Xóa tin
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($isPaginator)
                    <div class="p-5 border-t border-white/10">
                        {{ $newsItems->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-guest-layout>