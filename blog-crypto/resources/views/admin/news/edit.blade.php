<x-guest-layout>
    @php
        $categoryList = $categories ?? collect();
        $article = $article ?? $newsItem ?? $news ?? null;

        $publishedValue = old('published_at');

        if (! $publishedValue && $article?->published_at) {
            try {
                $publishedValue = \Carbon\Carbon::parse($article->published_at)->format('Y-m-d\TH:i');
            } catch (\Throwable $e) {
                $publishedValue = null;
            }
        }
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-blue-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
            </div>

            <div class="relative p-6 md:p-10">
                <a href="{{ route('admin.news.index') }}"
                   class="inline-flex items-center text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                    ← Quay lại quản lý tin tức
                </a>

                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-sm text-blue-200">
                    <span class="h-2 w-2 rounded-full bg-blue-300"></span>
                    Edit news
                </div>

                <h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight break-words [overflow-wrap:anywhere]">
                    Chỉnh sửa tin tức
                </h1>

                <p class="mt-4 max-w-2xl text-slate-300 leading-relaxed break-words [overflow-wrap:anywhere]">
                    {{ $article?->title ?? 'Cập nhật nội dung tin tức trong hệ thống.' }}
                </p>
            </div>
        </section>

        @if ($errors->any())
            <section class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                <div class="font-semibold mb-2">Có lỗi xảy ra:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if (! $article)
            <section class="rounded-3xl border border-rose-400/20 bg-rose-400/10 p-6 text-rose-100">
                Không tìm thấy dữ liệu tin tức để chỉnh sửa.
            </section>
        @else
            <form method="POST" action="{{ route('admin.news.update', $article->id) }}" class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 md:p-8 space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Tiêu đề tin tức</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $article->title) }}"
                           required
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">Chuyên mục</label>
                        <select name="category_id"
                                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                            <option value="">Chưa phân loại</option>
                            @foreach ($categoryList as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">Nguồn tin</label>
                        <input type="text"
                               name="source"
                               value="{{ old('source', $article->source) }}"
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">Thời gian xuất bản</label>
                        <input type="datetime-local"
                               name="published_at"
                               value="{{ $publishedValue }}"
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Ảnh thumbnail URL</label>
                    <input type="url"
                           name="thumbnail"
                           value="{{ old('thumbnail', $article->thumbnail) }}"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30"
                           placeholder="https://...">
                </div>

                @if ($article->thumbnail)
                    <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500 mb-3">Thumbnail hiện tại</div>
                        <img src="{{ $article->thumbnail }}"
                             alt="{{ $article->title }}"
                             class="max-h-64 rounded-2xl border border-white/10 object-cover">
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Tóm tắt</label>
                    <textarea name="summary"
                              rows="4"
                              class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">{{ old('summary', $article->summary) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Nội dung</label>
                    <textarea name="content"
                              rows="12"
                              required
                              class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30">{{ old('content', $article->content) }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-white/10">
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.news.index') }}"
                           class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Hủy
                        </a>

                        @if (! empty($article->slug))
                            <a href="{{ route('news.show', $article->slug) }}"
                               class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-3 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                Xem public
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>