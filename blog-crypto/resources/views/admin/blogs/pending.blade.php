@section('title', 'Duyệt blog - CryptoBlog')

<x-guest-layout>
    @php
        $postItems = $posts ?? $pendingPosts ?? collect();
        $isPaginator = is_object($postItems) && method_exists($postItems, 'total');
        $postCount = $isPaginator ? $postItems->total() : $postItems->count();

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
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-orange-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-orange-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-orange-400/20 bg-orange-400/10 px-3 py-1 text-sm text-orange-200">
                        <span class="h-2 w-2 rounded-full bg-orange-300 shadow-[0_0_14px_rgba(251,146,60,0.9)]"></span>
                        Blog moderation
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Duyệt
                        <span class="bg-gradient-to-r from-orange-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                            bài viết Blog
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Kiểm tra nội dung, chuyên mục, thumbnail và ảnh minh họa của các bài viết do Author gửi lên trước khi công khai trên website.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            ← Dashboard
                        </a>

                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Xem trang Blog
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Bài chờ duyệt</div>
                                <div class="mt-1 text-4xl font-black text-white">{{ number_format($postCount) }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-orange-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 11L10.2 14.2L17 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M5 4H19V20H5V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4">
                            <div class="text-sm font-semibold text-yellow-200">
                                Lưu ý kiểm duyệt
                            </div>

                            <p class="mt-1 text-sm text-slate-300 leading-6">
                                Hãy kiểm tra kỹ nội dung, ảnh minh họa và mức độ phù hợp trước khi duyệt bài công khai.
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

            @if (session('error'))
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    <div class="font-semibold mb-2">Có lỗi xảy ra:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- LIST --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl shadow-slate-950/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 md:p-6 border-b border-white/10">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-white">
                        Danh sách bài viết chờ duyệt
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Bài viết sau khi được duyệt sẽ hiển thị công khai trên trang Blog.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                    Tổng:
                    <span class="font-bold text-white">{{ number_format($postCount) }}</span>
                    bài
                </div>
            </div>

            @if ($postItems->count() === 0)
                <div class="p-10 text-center">
                    <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                        <span class="text-3xl">✅</span>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-white">
                        Không có bài viết nào đang chờ duyệt
                    </h3>

                    <p class="mt-2 text-slate-400">
                        Hiện tại tất cả bài viết đã được xử lý hoặc chưa có Author nào gửi bài mới.
                    </p>

                    <a href="{{ route('admin.dashboard') }}"
                       class="mt-5 inline-flex rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        Quay lại Dashboard
                    </a>
                </div>
            @else
                <div class="divide-y divide-white/10">
                    @foreach ($postItems as $post)
                        @php
                            $rawContent = trim((string) ($post->content ?? ''));
                            $hasRichTextMarkup = $rawContent !== strip_tags($rawContent);
                            $inlineImageCount = preg_match_all('/<img\b/i', $rawContent);
                            $legacyImageCount = $post->images?->count() ?? 0;
                            $totalImageCount = $inlineImageCount + $legacyImageCount;

                            $commentCount = method_exists($post, 'comments')
                                ? ($post->relationLoaded('comments') ? $post->comments->count() : $post->comments()->count())
                                : 0;

                            $reactionCount = method_exists($post, 'reactions')
                                ? ($post->relationLoaded('reactions') ? $post->reactions->count() : $post->reactions()->count())
                                : 0;
                        @endphp

                        <article class="p-5 md:p-6 hover:bg-white/[0.025] transition">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                                {{-- THUMBNAIL --}}
                                <div class="lg:col-span-4">
                                    <div class="sticky top-28 space-y-4">
                                        <div class="h-64 rounded-3xl overflow-hidden border border-white/10 bg-slate-950/60">
                                            @if ($post->thumbnail)
                                                <img src="{{ $post->thumbnail }}"
                                                     alt="{{ $post->title }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full bg-gradient-to-br from-orange-950 via-slate-900 to-cyan-950 flex items-center justify-center">
                                                    <div class="h-20 w-20 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl font-black text-white">
                                                        {{ strtoupper(mb_substr($post->title, 0, 1)) }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 space-y-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-sm text-slate-400">Trạng thái</span>
                                                <span class="rounded-full border border-yellow-400/20 bg-yellow-400/10 px-3 py-1 text-xs font-bold text-yellow-200">
                                                    Chờ duyệt
                                                </span>
                                            </div>

                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-sm text-slate-400">Bình luận</span>
                                                <span class="text-sm font-bold text-white">{{ $commentCount }}</span>
                                            </div>

                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-sm text-slate-400">Lượt thích</span>
                                                <span class="text-sm font-bold text-white">{{ $reactionCount }}</span>
                                            </div>

                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-sm text-slate-400">Tổng ảnh trong bài</span>
                                                <span class="text-sm font-bold text-white">
                                                    {{ $totalImageCount }}
                                                </span>
                                            </div>

                                            <div class="rounded-2xl border border-cyan-400/15 bg-cyan-400/5 px-4 py-3 text-xs text-slate-400 leading-5">
                                                Ảnh chèn giữa nội dung: <span class="font-bold text-cyan-200">{{ $inlineImageCount }}</span><br>
                                                Ảnh minh họa kiểu cũ: <span class="font-bold text-cyan-200">{{ $legacyImageCount }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- CONTENT --}}
                                <div class="lg:col-span-8 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border border-orange-400/20 bg-orange-400/10 px-3 py-1 text-xs font-bold text-orange-200">
                                            {{ $post->category?->name ?? 'Chưa phân loại' }}
                                        </span>

                                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                            Tác giả: {{ $post->author?->name ?? $post->user?->name ?? 'Không rõ' }}
                                        </span>

                                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-400">
                                            Gửi lúc: {{ $formatDate($post->created_at) }}
                                        </span>
                                    </div>

                                    <h3 class="mt-4 text-3xl font-black text-white leading-tight break-words [overflow-wrap:anywhere] text-left">
                                        {{ $post->title }}
                                    </h3>

                                    {{-- FULL ARTICLE PREVIEW --}}
                                    <div class="mt-6 overflow-hidden rounded-3xl border border-cyan-400/20 bg-slate-950/55 shadow-xl shadow-slate-950/20">
                                        <div class="flex flex-col gap-3 border-b border-white/10 bg-cyan-400/5 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <div class="text-sm font-black text-white">
                                                    Bản xem trước đầy đủ
                                                </div>
                                                <p class="mt-1 text-xs text-slate-400">
                                                    Nội dung dưới đây được hiển thị đúng định dạng tác giả đã soạn, bao gồm đề mục, trích dẫn, liên kết và ảnh chèn giữa bài.
                                                </p>
                                            </div>

                                            <span class="inline-flex w-fit items-center rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-200">
                                                {{ $inlineImageCount }} ảnh trong nội dung
                                            </span>
                                        </div>

                                        <div class="admin-article-preview p-5 md:p-7">
                                            <div class="article-content mx-0 max-w-none text-left">
                                                @if ($rawContent === '')
                                                    <p class="text-left text-slate-400">
                                                        Bài viết chưa có nội dung.
                                                    </p>
                                                @elseif ($hasRichTextMarkup)
                                                    {!! $rawContent !!}
                                                @else
                                                    <p class="whitespace-pre-line text-left text-slate-200 leading-8 break-words [overflow-wrap:anywhere]">
                                                        {{ $rawContent }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- BLOG IMAGES GALLERY --}}
                                    @include('partials.blog-images-gallery', ['post' => $post])

                                    {{-- ADMIN ACTIONS --}}
                                    <div class="mt-6 rounded-3xl border border-white/10 bg-slate-950/60 p-5">
                                        <h4 class="text-xl font-black text-white">
                                            Hành động kiểm duyệt
                                        </h4>

                                        <p class="mt-2 text-sm text-slate-400 leading-7">
                                            Admin có thể duyệt bài nếu nội dung phù hợp, hoặc từ chối kèm lý do để Author chỉnh sửa lại.
                                        </p>

                                        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            {{-- APPROVE --}}
                                            <form method="POST"
                                                  action="{{ route('admin.blog.approve', $post->id) }}"
                                                  onsubmit="return confirm('Bạn có chắc muốn duyệt bài viết này không?')"
                                                  class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                                                @csrf
                                                @method('PATCH')

                                                <div class="text-sm font-bold text-emerald-200">
                                                    Duyệt bài viết
                                                </div>

                                                <p class="mt-2 text-sm text-slate-300 leading-6">
                                                    Bài viết sẽ được chuyển sang trạng thái approved và hiển thị công khai trên trang Blog.
                                                </p>

                                                <button type="submit"
                                                        class="mt-4 w-full rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                                                    Duyệt bài
                                                </button>
                                            </form>

                                            {{-- REJECT --}}
                                            <form method="POST"
                                                  action="{{ route('admin.blog.reject', $post->id) }}"
                                                  onsubmit="return confirm('Bạn có chắc muốn từ chối bài viết này không?')"
                                                  class="rounded-3xl border border-rose-400/20 bg-rose-400/10 p-5">
                                                @csrf
                                                @method('PATCH')

                                                <div class="text-sm font-bold text-rose-200">
                                                    Từ chối bài viết
                                                </div>

                                                <p class="mt-2 text-sm text-slate-300 leading-6">
                                                    Lý do từ chối sẽ được hiển thị cho Author để họ biết cần sửa gì.
                                                </p>

                                                <label class="mt-4 block text-sm font-bold text-slate-200 mb-2">
                                                    Lý do từ chối
                                                </label>

                                                <textarea name="rejection_reason"
                                                          rows="4"
                                                          class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-rose-400 focus:ring-rose-400/30"
                                                          placeholder="VD: Bài viết cần bổ sung nguồn tham khảo, nội dung còn quá ngắn hoặc ảnh minh họa chưa phù hợp..."></textarea>

                                                <button type="submit"
                                                        class="mt-4 w-full rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 hover:from-rose-400 hover:to-orange-400 transition">
                                                    Từ chối bài
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($isPaginator)
                    <div class="p-5 border-t border-white/10">
                        {{ $postItems->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-guest-layout>