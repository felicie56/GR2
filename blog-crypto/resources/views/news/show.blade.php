<x-guest-layout>
    @php
        $article = $newsItem ?? $article ?? $news ?? null;

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

        $comments = $article?->comments ?? collect();
        $contentBlocks = $contentBlocks ?? [];
        $relatedLinks = $relatedLinks ?? collect();
        $inlineRelatedLinks = $inlineRelatedLinks ?? collect();
    @endphp

    @if (! $article)
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <section class="rounded-[2rem] border border-rose-400/20 bg-rose-400/10 p-8 text-rose-100">
                Không tìm thấy dữ liệu tin tức.
            </section>
        </div>
    @else
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <article class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20">
                {{-- HERO --}}
                <div class="relative min-h-[320px] md:min-h-[480px] bg-slate-900 overflow-hidden">
                    @if ($article->thumbnail)
                        <img
                            src="{{ $article->thumbnail }}"
                            alt="{{ $article->title }}"
                            class="absolute inset-0 h-full w-full object-cover"
                        >

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/30 via-cyan-500/20 to-indigo-500/30"></div>
                        <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>

                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="h-28 w-28 rounded-[2rem] bg-white/10 border border-white/20 flex items-center justify-center text-5xl font-black text-white">
                                {{ strtoupper(mb_substr($article->title, 0, 1)) }}
                            </div>
                        </div>
                    @endif

                    <div class="absolute left-0 right-0 bottom-0 p-6 md:p-10">
                        <a
                            href="{{ route('news.index') }}"
                            class="inline-flex mb-5 text-sm font-semibold text-cyan-300 hover:text-cyan-200"
                        >
                            ← Quay lại Tin tức
                        </a>

                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="rounded-full bg-blue-400/15 text-blue-100 ring-1 ring-blue-300/30 px-3 py-1 text-xs font-semibold backdrop-blur">
                                {{ $article->category?->name ?? 'Chưa phân loại' }}
                            </span>

                            @if ($article->is_auto)
                                <span class="rounded-full bg-emerald-400/15 text-emerald-100 ring-1 ring-emerald-300/30 px-3 py-1 text-xs font-bold backdrop-blur">
                                    Auto RSS
                                </span>
                            @else
                                <span class="rounded-full bg-indigo-400/15 text-indigo-100 ring-1 ring-indigo-300/30 px-3 py-1 text-xs font-bold backdrop-blur">
                                    Manual
                                </span>
                            @endif

                            @if ($article->source)
                                <span class="rounded-full bg-slate-950/70 text-slate-300 ring-1 ring-white/10 px-3 py-1 text-xs font-semibold backdrop-blur">
                                    {{ $article->source }}
                                </span>
                            @endif

                            <span class="rounded-full bg-slate-950/70 text-slate-300 ring-1 ring-white/10 px-3 py-1 text-xs font-semibold backdrop-blur">
                                {{ $formatDate($article->published_at ?: $article->created_at) }}
                            </span>
                        </div>

                        <h1 class="max-w-4xl text-3xl md:text-5xl font-black text-white leading-tight break-words [overflow-wrap:anywhere] text-left">
                            {{ $article->title }}
                        </h1>

                        @if ($article->summary)
                            <p class="mt-5 max-w-3xl text-slate-200 leading-7 break-words [overflow-wrap:anywhere] text-left">
                                {{ $article->summary }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- META --}}
                <div class="p-6 md:p-10 border-b border-white/10">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Nguồn tin</div>
                            <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                                {{ $article->source ?: 'CryptoBlog' }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Chuyên mục</div>
                            <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                                {{ $article->category?->name ?? 'Chưa phân loại' }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Bình luận</div>
                            <div class="mt-1 text-sm font-semibold text-white">
                                {{ $comments->count() }} bình luận
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-xs text-slate-500">Tin đã liên kết</div>
                            <div class="mt-1 text-sm font-semibold text-white">
                                {{ $relatedLinks->count() }} bài liên quan
                            </div>
                        </div>
                    </div>

                    @if ($article->is_auto || $article->source_url)
                        <div class="mt-6 rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <div class="text-sm font-bold text-emerald-200">
                                        Tin tức tự động từ nguồn bên ngoài
                                    </div>

                                    <p class="mt-2 text-sm text-slate-300 leading-7">
                                        Tin này được hệ thống tự động tổng hợp từ RSS/API, lưu lại nguồn xuất bản và phân tích nội dung để liên kết với các tin trước đó có cùng chủ đề.
                                    </p>
                                </div>

                                @if ($article->source_url)
                                    <a
                                        href="{{ $article->source_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer nofollow"
                                        class="shrink-0 inline-flex justify-center rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition"
                                    >
                                        Đọc bài gốc →
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- CONTENT --}}
                <div class="p-6 md:p-10">
                    <div class="article-content mx-auto max-w-3xl text-left">
                        @if ($contentBlocks === [])
                            <p class="text-left text-slate-300 leading-8">
                                Nội dung tin tức đang được cập nhật.
                            </p>
                        @else
                            @foreach ($contentBlocks as $blockIndex => $contentBlock)
                                <div class="news-content-block">
                                    {!! $contentBlock !!}
                                </div>

                                @foreach ($inlineRelatedLinks->get($blockIndex + 1, collect()) as $relation)
                                    @include('partials.news-related-inline', [
                                        'relation' => $relation,
                                    ])
                                @endforeach
                            @endforeach
                        @endif
                    </div>

                    @if ($article->source_url)
                        <section class="mt-10 rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5 md:p-6">
                            <div class="text-sm font-bold text-emerald-200">
                                Nguồn tin gốc
                            </div>

                            <p class="mt-2 text-sm text-slate-300 leading-7">
                                Nội dung trên CryptoBlog là bản tổng hợp. Hãy mở nguồn gốc để kiểm tra đầy đủ dữ kiện, phát biểu và bối cảnh của bài viết.
                            </p>

                            <a
                                href="{{ $article->source_url }}"
                                target="_blank"
                                rel="noopener noreferrer nofollow"
                                class="mt-4 inline-flex rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition"
                            >
                                Đọc bài gốc tại {{ $article->source ?? 'nguồn tin' }}
                            </a>
                        </section>
                    @endif

                    @if ($relatedLinks->isNotEmpty())
                        <section class="mt-10 rounded-[2rem] border border-white/10 bg-slate-950/45 p-5 md:p-7">
                            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                                <div>
                                    <div class="text-xs font-black uppercase tracking-[0.16em] text-cyan-300">
                                        Internal linking
                                    </div>

                                    <h2 class="mt-2 text-2xl font-black text-white">
                                        Các tin trước đó có liên quan
                                    </h2>

                                    <p class="mt-2 text-sm leading-6 text-slate-400">
                                        Danh sách được hệ thống tự động chấm điểm dựa trên chuyên mục, thực thể, từ khóa, độ giống tiêu đề và độ mới của tin.
                                    </p>
                                </div>

                                @if ($article->related_links_generated_at)
                                    <div class="text-xs text-slate-500">
                                        Phân tích lúc {{ $article->related_links_generated_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($relatedLinks as $relation)
                                    @php
                                        $relatedArticle = $relation->relatedNews;
                                    @endphp

                                    @if ($relatedArticle)
                                        <a
                                            href="{{ route('news.show', $relatedArticle->slug) }}"
                                            class="group rounded-3xl border border-white/10 bg-white/[0.035] p-5 hover:border-cyan-300/30 hover:bg-cyan-400/[0.06] transition"
                                        >
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="rounded-full border border-blue-300/20 bg-blue-300/10 px-3 py-1 text-xs font-bold text-blue-200">
                                                    {{ $relatedArticle->category?->name ?? 'Tin tức' }}
                                                </span>

                                                <span class="text-xs text-slate-500">
                                                    {{ number_format((float) $relation->score, 1) }} điểm
                                                </span>
                                            </div>

                                            <h3 class="mt-4 text-base font-black leading-6 text-white group-hover:text-cyan-200 transition break-words [overflow-wrap:anywhere]">
                                                {{ $relatedArticle->title }}
                                            </h3>

                                            @if ($relation->reason)
                                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                                    {{ $relation->reason }}
                                                </p>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                        <div>
                            <div class="text-sm font-semibold text-white">
                                Lưu ý nội dung
                            </div>

                            <p class="mt-1 text-sm text-slate-400">
                                Nội dung chỉ mang tính tham khảo, không phải lời khuyên đầu tư.
                            </p>
                        </div>

                        <a
                            href="{{ route('news.index') }}"
                            class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition"
                        >
                            ← Quay lại Tin tức
                        </a>
                    </div>
                </div>
            </article>

            @include('partials.comments-section', [
                'comments' => $comments,
                'storeRoute' => route('news.comments.store', $article->id),
            ])
        </div>
    @endif
</x-guest-layout>