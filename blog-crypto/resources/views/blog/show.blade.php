@section('title', ($post->title ?? 'Chi tiết Blog') . ' - CryptoBlog')

<x-guest-layout>
    @php
        $comments = $post->comments ?? collect();
        $reactions = $post->reactions ?? collect();

        $contentParagraphs = collect();

        if ($post->content) {
            $contentParagraphs = collect(preg_split('/\R{2,}/', trim($post->content)))
                ->map(fn ($paragraph) => trim(preg_replace('/\s+/', ' ', $paragraph)))
                ->filter()
                ->values();
        }

        $hasLiked = false;

        if (auth()->check()) {
            $hasLiked = $reactions->contains('user_id', auth()->id());
        }
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <article class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20">
            {{-- HERO IMAGE --}}
            <div class="relative min-h-[300px] md:min-h-[460px] bg-slate-900 overflow-hidden">
                @if ($post->thumbnail)
                    <img src="{{ $post->thumbnail }}"
                         alt="{{ $post->title }}"
                         class="absolute inset-0 h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/55 to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/30 via-cyan-500/20 to-emerald-500/30"></div>
                    <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="h-28 w-28 rounded-[2rem] bg-white/10 border border-white/20 flex items-center justify-center text-5xl font-black text-white">
                            {{ strtoupper(mb_substr($post->title, 0, 1)) }}
                        </div>
                    </div>
                @endif

                <div class="absolute left-0 right-0 bottom-0 p-6 md:p-10">
                    <a href="{{ route('blog.index') }}"
                       class="inline-flex mb-5 text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                        ← Quay lại Blog
                    </a>

                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="rounded-full bg-indigo-400/15 text-indigo-100 ring-1 ring-indigo-300/30 px-3 py-1 text-xs font-semibold backdrop-blur">
                            {{ $post->category?->name ?? 'Chưa phân loại' }}
                        </span>

                        <span class="rounded-full bg-slate-950/70 text-slate-300 ring-1 ring-white/10 px-3 py-1 text-xs font-semibold backdrop-blur">
                            {{ $post->reviewed_at ? $post->reviewed_at->format('d/m/Y') : $post->created_at->format('d/m/Y') }}
                        </span>
                    </div>

                    <h1 class="max-w-4xl text-left text-3xl md:text-5xl font-black text-white leading-tight break-words [overflow-wrap:anywhere]">
                        {{ $post->title }}
                    </h1>
                </div>
            </div>

            {{-- META --}}
            <div class="p-6 md:p-10 border-b border-white/10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Tác giả</div>
                        <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                            {{ $post->author?->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Bình luận</div>
                        <div id="blog-comment-count-stat" class="mt-1 text-sm font-semibold text-white">
                            {{ $comments->count() }} bình luận
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Lượt thích</div>
                        <div id="blog-like-count-stat" class="mt-1 text-sm font-semibold text-white">
                            {{ $reactions->count() }} lượt thích
                        </div>
                    </div>
                </div>

                @auth
                    <form method="POST"
                          action="{{ route('blog.like', $post->id) }}"
                          class="js-like-form mt-5">
                        @csrf

                        <button type="submit"
                                id="blog-like-button"
                                class="inline-flex items-center rounded-2xl border border-pink-400/20 bg-pink-500/15 px-5 py-3 text-sm font-bold text-pink-100 hover:bg-pink-500/25 transition">
                            <span id="blog-like-icon">{{ $hasLiked ? '💖' : '❤️' }}</span>
                            <span class="ml-2" id="blog-like-label">
                                {{ $hasLiked ? 'Đã thích' : 'Thích bài viết' }}
                            </span>
                        </button>

                        <p id="blog-like-message" class="hidden mt-2 text-sm text-emerald-300"></p>
                    </form>
                @else
                    <div class="mt-5 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                        <p class="text-sm text-slate-300">
                            Bạn cần đăng nhập để thích bài viết.
                        </p>
                    </div>
                @endauth
            </div>

            {{-- CONTENT --}}
            <div class="p-6 md:p-10">
                <div class="article-content mx-0 max-w-none space-y-5 text-left">
                    @forelse ($contentParagraphs as $paragraph)
                        <p class="text-left text-slate-200 leading-8 break-words [overflow-wrap:anywhere]">
                            {{ $paragraph }}
                        </p>
                    @empty
                        <p class="text-left text-slate-300 leading-8">
                            Nội dung bài viết đang được cập nhật.
                        </p>
                    @endforelse
                </div>

                @include('partials.blog-images-gallery', ['post' => $post])
                
                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                    <div>
                        <div class="text-sm font-semibold text-white">
                            Lưu ý nội dung
                        </div>

                        <p class="mt-1 text-sm text-slate-400">
                            Bài viết chỉ mang tính tham khảo, không phải lời khuyên đầu tư.
                        </p>
                    </div>

                    <a href="{{ route('blog.index') }}"
                       class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        ← Quay lại Blog
                    </a>
                </div>
            </div>
        </article>

        @include('partials.comments-section', [
            'comments' => $comments,
            'storeRoute' => route('blog.comments.store', $post->id),
        ])
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const likeForm = document.querySelector('.js-like-form');

            if (!likeForm) {
                return;
            }

            const likeButton = document.getElementById('blog-like-button');
            const likeIcon = document.getElementById('blog-like-icon');
            const likeLabel = document.getElementById('blog-like-label');
            const likeCountStat = document.getElementById('blog-like-count-stat');
            const likeMessage = document.getElementById('blog-like-message');

            likeForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (!likeButton) {
                    return;
                }

                likeButton.disabled = true;
                likeButton.classList.add('opacity-60', 'cursor-not-allowed');

                if (likeMessage) {
                    likeMessage.classList.add('hidden');
                    likeMessage.textContent = '';
                }

                try {
                    const response = await fetch(likeForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: new FormData(likeForm),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Không thể xử lý lượt thích.');
                    }

                    if (likeCountStat && typeof data.like_count !== 'undefined') {
                        likeCountStat.textContent = data.like_count + ' lượt thích';
                    }

                    if (likeIcon) {
                        likeIcon.textContent = data.liked ? '💖' : '❤️';
                    }

                    if (likeLabel) {
                        likeLabel.textContent = data.liked ? 'Đã thích' : 'Thích bài viết';
                    }

                    if (likeMessage) {
                        likeMessage.textContent = data.message || 'Đã cập nhật lượt thích.';
                        likeMessage.classList.remove('hidden');

                        setTimeout(function () {
                            likeMessage.classList.add('hidden');
                        }, 1800);
                    }
                } catch (error) {
                    if (likeMessage) {
                        likeMessage.textContent = error.message || 'Không thể xử lý lượt thích.';
                        likeMessage.classList.remove('hidden');
                        likeMessage.classList.remove('text-emerald-300');
                        likeMessage.classList.add('text-rose-300');
                    }
                } finally {
                    likeButton.disabled = false;
                    likeButton.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            });
        });
    </script>
</x-guest-layout>