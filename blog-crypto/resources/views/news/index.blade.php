<x-guest-layout>
    @section('title', 'Tin tức Crypto - CryptoBlog')
    @php
        $categoryList = $categories ?? collect();
        $newsItems = $news->getCollection();
        $featuredNews = $newsItems->first();
        $remainingNews = $newsItems->skip(1);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-blue-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-20 -right-20 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10 lg:p-12">
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-sm text-blue-200">
                        <span class="h-2 w-2 rounded-full bg-blue-300 shadow-[0_0_14px_rgba(147,197,253,0.9)]"></span>
                        Market news center
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
                        Tin tức tài chính &
                        <span class="bg-gradient-to-r from-blue-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                            Crypto
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-base md:text-lg text-slate-300 leading-relaxed">
                        Cập nhật các thông tin mới về thị trường crypto, blockchain, DeFi, stablecoin và các xu hướng tài chính số.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('crypto.index') }}"
                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                            Xem giá crypto
                        </a>

                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Đọc blog phân tích
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Tin tức trong hệ thống</div>
                                <div class="mt-1 text-3xl font-black text-white">{{ $news->total() }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 4H16L19 7V20H5V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M8 9H16M8 12H16M8 15H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                <div class="text-xs text-slate-500">Chuyên mục</div>
                                <div class="mt-1 text-xl font-bold text-white">{{ $categoryList->count() }}</div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                <div class="text-xs text-slate-500">Nguồn tin</div>
                                <div class="mt-1 text-xl font-bold text-white">Curated</div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-blue-400/20 bg-blue-400/10 p-4">
                            <div class="text-sm font-semibold text-blue-200">
                                Gợi ý
                            </div>
                            <p class="mt-1 text-sm text-slate-300">
                                Hãy dùng bộ lọc để tìm tin theo Bitcoin, Ethereum, DeFi, stablecoin hoặc các chuyên mục thị trường.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SEARCH + FILTER --}}
        <section class="mt-8 rounded-3xl border border-white/10 bg-white/[0.04] p-5 md:p-6 backdrop-blur-xl">
            <form method="GET" action="{{ route('news.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                <div class="lg:col-span-6">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Tìm kiếm tin tức
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Ví dụ: Bitcoin, Fed, Ethereum, DeFi..."
                        class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30"
                    >
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Chuyên mục
                    </label>

                    <select
                        name="category"
                        class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30"
                    >
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

                    <a href="{{ route('news.index') }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/10 transition">
                        Xóa
                    </a>
                </div>
            </form>

            @if ($categoryList->count() > 0)
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('news.index', array_filter(['search' => $search])) }}"
                       class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ empty($categoryId) ? 'bg-blue-400/15 text-blue-200 ring-1 ring-blue-400/30' : 'bg-white/[0.04] text-slate-300 hover:bg-white/10' }}">
                        Tất cả
                    </a>

                    @foreach ($categoryList as $category)
                        <a href="{{ route('news.index', array_filter(['search' => $search, 'category' => $category->id])) }}"
                           class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ (string) $categoryId === (string) $category->id ? 'bg-blue-400/15 text-blue-200 ring-1 ring-blue-400/30' : 'bg-white/[0.04] text-slate-300 hover:bg-white/10' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- RESULT HEADER --}}
        <div class="mt-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-white">
                    Tin tức mới nhất
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    @if ($search || $categoryId)
                        Kết quả phù hợp với bộ lọc hiện tại.
                    @else
                        Các tin tức mới được cập nhật trong hệ thống.
                    @endif
                </p>
            </div>

            <div class="text-sm text-slate-500">
                Hiển thị {{ $news->count() }} / {{ $news->total() }} tin tức
            </div>
        </div>

        {{-- EMPTY --}}
        @if ($newsItems->count() === 0)
            <div class="mt-6 rounded-3xl border border-white/10 bg-white/[0.04] p-10 text-center">
                <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-900 border border-white/10 flex items-center justify-center">
                    <span class="text-3xl">📰</span>
                </div>

                <h3 class="mt-5 text-xl font-bold text-white">
                    Chưa có tin tức phù hợp
                </h3>

                <p class="mt-2 text-slate-400">
                    Hãy thử đổi từ khóa tìm kiếm hoặc chọn một chuyên mục khác.
                </p>

                <a href="{{ route('news.index') }}"
                   class="inline-flex mt-5 rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white">
                    Xem tất cả tin tức
                </a>
            </div>
        @else

            {{-- FEATURED NEWS --}}
            @if ($featuredNews)
                <section class="mt-6">
                    <article class="group overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20 hover:border-blue-400/30 transition">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            <div class="lg:col-span-5 min-h-[280px] bg-slate-900 relative overflow-hidden">
                                @if ($featuredNews->thumbnail)
                                    <img src="{{ $featuredNews->thumbnail }}"
                                         alt="{{ $featuredNews->title }}"
                                         class="h-full w-full object-cover group-hover:scale-105 transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                                @else
                                    <div class="h-full w-full bg-gradient-to-br from-blue-600/40 via-cyan-500/20 to-indigo-400/30 flex items-center justify-center">
                                        <div class="h-24 w-24 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center text-4xl font-black text-white">
                                            {{ strtoupper(mb_substr($featuredNews->title, 0, 1)) }}
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute left-5 top-5">
                                    <span class="rounded-full bg-slate-950/80 border border-white/10 px-3 py-1 text-xs font-semibold text-blue-200 backdrop-blur">
                                        Featured News
                                    </span>
                                </div>
                            </div>

                            <div class="lg:col-span-7 p-6 md:p-8 lg:p-10">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-blue-400/10 text-blue-200 ring-1 ring-blue-400/20 px-3 py-1 text-xs font-semibold">
                                        {{ $featuredNews->category?->name ?? 'Chưa phân loại' }}
                                    </span>

                                    @if ($featuredNews->source)
                                        <span class="rounded-full bg-white/[0.04] text-slate-300 ring-1 ring-white/10 px-3 py-1 text-xs font-semibold">
                                            {{ $featuredNews->source }}
                                        </span>
                                    @endif

                                    <span class="text-xs text-slate-500">
                                        {{ $featuredNews->published_at ? $featuredNews->published_at->format('d/m/Y H:i') : $featuredNews->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <h3 class="mt-4 text-3xl md:text-4xl font-black text-white leading-tight break-words [overflow-wrap:anywhere]">
                                    <a href="{{ route('news.show', $featuredNews->slug) }}" class="hover:text-blue-200 transition">
                                        {{ $featuredNews->title }}
                                    </a>
                                </h3>

                                <p class="mt-4 text-slate-300 leading-relaxed break-words [overflow-wrap:anywhere]">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($featuredNews->summary ?: $featuredNews->content), 260) }}
                                </p>

                                <a href="{{ route('news.show', $featuredNews->slug) }}"
                                   class="inline-flex mt-7 items-center rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                                    Đọc tin tức
                                    <span class="ml-2">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                </section>
            @endif

            {{-- NEWS GRID --}}
            @if ($remainingNews->count() > 0)
                <section class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach ($remainingNews as $article)
                        <article class="group overflow-hidden rounded-3xl border border-white/10 bg-white/[0.04] hover:border-blue-400/30 hover:bg-white/[0.06] transition">
                            <div class="h-48 bg-slate-900 relative overflow-hidden">
                                @if ($article->thumbnail)
                                    <img src="{{ $article->thumbnail }}"
                                         alt="{{ $article->title }}"
                                         class="h-full w-full object-cover group-hover:scale-105 transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                                @else
                                    <div class="h-full w-full bg-gradient-to-br from-slate-800 via-blue-950 to-cyan-950 flex items-center justify-center">
                                        <div class="h-16 w-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl font-black text-white">
                                            {{ strtoupper(mb_substr($article->title, 0, 1)) }}
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute left-4 bottom-4">
                                    <span class="rounded-full bg-slate-950/80 border border-white/10 px-3 py-1 text-xs font-semibold text-blue-200 backdrop-blur">
                                        {{ $article->category?->name ?? 'Chưa phân loại' }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($article->source)
                                        <span class="rounded-full bg-white/[0.04] text-slate-400 ring-1 ring-white/10 px-2.5 py-1 text-xs">
                                            {{ $article->source }}
                                        </span>
                                    @endif

                                    <span class="text-xs text-slate-500">
                                        {{ $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y') }}
                                    </span>
                                </div>

                                <h3 class="mt-3 text-xl font-bold text-white leading-snug break-words [overflow-wrap:anywhere]">
                                    <a href="{{ route('news.show', $article->slug) }}" class="hover:text-blue-200 transition">
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <p class="mt-3 text-sm text-slate-400 leading-relaxed break-words [overflow-wrap:anywhere]">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($article->summary ?: $article->content), 140) }}
                                </p>

                                <div class="mt-5 flex items-center justify-end">
                                    <a href="{{ route('news.show', $article->slug) }}"
                                       class="rounded-xl border border-white/10 px-3 py-2 text-sm font-semibold text-blue-200 hover:bg-blue-400/10 transition">
                                        Đọc tin →
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif

            {{-- PAGINATION --}}
            <div class="mt-8">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</x-guest-layout>