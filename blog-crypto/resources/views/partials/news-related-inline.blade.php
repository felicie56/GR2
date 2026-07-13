@php
    $relatedArticle = $relation?->relatedNews;
@endphp

@if ($relatedArticle)
    <aside class="my-8 overflow-hidden rounded-3xl border border-cyan-400/20 bg-gradient-to-r from-cyan-400/10 via-blue-500/10 to-indigo-500/10 shadow-lg shadow-cyan-950/20">
        <div class="flex items-stretch">
            <div class="w-1.5 shrink-0 bg-gradient-to-b from-cyan-300 via-blue-400 to-indigo-500"></div>

            <div class="min-w-0 flex-1 p-5 md:p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-cyan-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>
                        Tin liên quan
                    </span>

                    @if ($relatedArticle->category)
                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300">
                            {{ $relatedArticle->category->name }}
                        </span>
                    @endif

                    @if ($relatedArticle->published_at || $relatedArticle->created_at)
                        <span class="text-xs text-slate-500">
                            {{ ($relatedArticle->published_at ?: $relatedArticle->created_at)->format('d/m/Y') }}
                        </span>
                    @endif
                </div>

                <a
                    href="{{ route('news.show', $relatedArticle->slug) }}"
                    class="mt-4 block text-lg md:text-xl font-black leading-snug text-white hover:text-cyan-200 transition break-words [overflow-wrap:anywhere]"
                >
                    {{ $relatedArticle->title }}
                </a>

                @if ($relation->reason)
                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        {{ $relation->reason }}
                    </p>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        {{ $relatedArticle->source ?: 'CryptoBlog' }}
                    </div>

                    <a
                        href="{{ route('news.show', $relatedArticle->slug) }}"
                        class="inline-flex items-center gap-2 text-sm font-bold text-cyan-300 hover:text-cyan-200 transition"
                    >
                        Xem tin trước đó
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </aside>
@endif