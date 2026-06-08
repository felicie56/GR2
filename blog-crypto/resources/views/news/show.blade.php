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

        $contentParagraphs = collect();

        if ($article && $article->content) {
            $contentParagraphs = collect(preg_split('/\R{2,}/', trim($article->content)))
                ->map(fn ($paragraph) => trim(preg_replace('/\s+/', ' ', $paragraph)))
                ->filter()
                ->values();
        }
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
                        <img src="{{ $article->thumbnail }}"
                             alt="{{ $article->title }}"
                             class="absolute inset-0 h-full w-full object-cover">
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
                        <a href="{{ route('news.index') }}"
                           class="inline-flex mb-5 text-sm font-semibold text-cyan-300 hover:text-cyan-200">
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    </div>

                    @if ($article->is_auto || $article->source_url)
                        <div class="mt-6 rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <div class="text-sm font-bold text-emerald-200">
                                        Tin tức tự động từ nguồn bên ngoài
                                    </div>

                                    <p class="mt-2 text-sm text-slate-300 leading-7">
                                        Tin này được hệ thống tự động tổng hợp từ RSS/API và được chuyển thành bản tóm tắt tiếng Việt.
                                        Nội dung trên CryptoBlog chỉ mang tính tham khảo, không sao chép toàn bộ bài viết gốc.
                                    </p>
                                </div>

                                @if ($article->source_url)
                                    <a href="{{ $article->source_url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="shrink-0 inline-flex justify-center rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                                        Đọc bài gốc →
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- CONTENT --}}
                <div class="p-6 md:p-10">
                    <div class="mx-auto max-w-3xl space-y-5 text-left">
                        @forelse ($contentParagraphs as $paragraph)
                            <p class="text-left text-slate-200 leading-8 break-words [overflow-wrap:anywhere]">
                                {{ $paragraph }}
                            </p>
                        @empty
                            <p class="text-left text-slate-300 leading-8">
                                Nội dung tin tức đang được cập nhật.
                            </p>
                        @endforelse
                    </div>

                    @if ($article->source_url)
                        <div class="mt-8 rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                            <div class="text-sm font-bold text-emerald-200">
                                Nguồn tin gốc
                            </div>

                            <p class="mt-2 text-sm text-slate-300 leading-7">
                                Người dùng có thể đọc bài viết đầy đủ tại nguồn gốc để xem thêm bối cảnh và thông tin chi tiết.
                            </p>

                            <a href="{{ $article->source_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mt-4 inline-flex rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                                Đọc bài gốc tại {{ $article->source ?? 'nguồn tin' }}
                            </a>
                        </div>
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

                        <a href="{{ route('news.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
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