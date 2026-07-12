@php
    $comments = $comments ?? collect();
    $storeRoute = $storeRoute ?? '#';
    $commentCount = method_exists($comments, 'count') ? $comments->count() : 0;
@endphp

<section class="mt-8 rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20 overflow-hidden">
    <div class="p-6 md:p-8 border-b border-white/10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-sm text-blue-200">
                    <span class="h-2 w-2 rounded-full bg-blue-300 shadow-[0_0_14px_rgba(147,197,253,0.9)]"></span>
                    Community discussion
                </div>

                <h2 class="mt-4 text-2xl md:text-3xl font-black text-white">
                    Bình luận
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Bình luận chỉ được hiển thị sau khi admin kiểm duyệt.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                Tổng:
                <span
                    id="comments-total-count"
                    class="font-bold text-white"
                >
                    {{ $commentCount }}
                </span>
                bình luận đã duyệt
            </div>
        </div>
    </div>

    <div class="p-6 md:p-8 border-b border-white/10">
        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        <div
            id="comment-success-message"
            class="hidden mb-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100"
        ></div>

        @auth
            <form
                method="POST"
                action="{{ $storeRoute }}"
                class="js-comment-form space-y-4"
            >
                @csrf

                <div class="flex items-start gap-4">
                    <div class="hidden sm:flex h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 items-center justify-center text-sm font-black text-white shadow-lg shadow-cyan-500/20 shrink-0">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <label
                            for="comment-content"
                            class="block text-sm font-bold text-slate-200 mb-2"
                        >
                            Viết bình luận
                        </label>

                        <textarea
                            id="comment-content"
                            name="content"
                            rows="4"
                            required
                            maxlength="2000"
                            class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-blue-400 focus:ring-blue-400/30"
                            placeholder="Nhập bình luận của bạn..."
                        >{{ old('content') }}</textarea>

                        <p class="js-comment-error hidden mt-2 text-sm text-rose-400"></p>

                        @error('content')
                            <p class="mt-2 text-sm text-rose-400">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <p class="text-xs text-slate-500">
                                Sau khi gửi, bình luận sẽ chuyển đến admin và chưa xuất hiện công khai ngay.
                            </p>

                            <button
                                type="submit"
                                class="js-comment-submit inline-flex justify-center rounded-2xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-blue-400 hover:to-cyan-400 transition"
                            >
                                Gửi bình luận
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="rounded-3xl border border-cyan-400/20 bg-cyan-400/10 p-5 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black text-white">
                            Đăng nhập để bình luận
                        </h3>

                        <p class="mt-2 text-sm text-slate-300">
                            Bạn cần đăng nhập để tham gia thảo luận.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('login') }}"
                            class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition"
                        >
                            Đăng nhập
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition"
                        >
                            Đăng ký
                        </a>
                    </div>
                </div>
            </div>
        @endauth
    </div>

    <div id="comments-wrapper">
        @if ($commentCount === 0)
            <div
                id="comments-empty-state"
                class="p-8 md:p-10 text-center"
            >
                <div class="mx-auto h-16 w-16 rounded-3xl bg-slate-950/70 border border-white/10 flex items-center justify-center">
                    <span class="text-3xl">💬</span>
                </div>

                <h3 class="mt-5 text-xl font-bold text-white">
                    Chưa có bình luận nào được duyệt
                </h3>

                <p class="mt-2 text-slate-400">
                    Các bình luận hợp lệ sẽ xuất hiện tại đây sau khi admin duyệt.
                </p>
            </div>
        @endif

        <div
            id="comments-list"
            class="divide-y divide-white/10"
        >
            @foreach ($comments as $comment)
                <article class="p-6 md:p-8 hover:bg-white/[0.025] transition">
                    <div class="flex items-start gap-4">
                        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-sm font-black text-white shrink-0 shadow-lg shadow-blue-500/20">
                            {{ strtoupper(mb_substr($comment->user?->name ?? 'U', 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-white break-words [overflow-wrap:anywhere]">
                                        {{ $comment->user?->name ?? 'Người dùng đã xóa' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $comment->created_at
                                            ? $comment->created_at->format('d/m/Y H:i')
                                            : 'Không rõ thời gian' }}
                                    </div>
                                </div>

                                @auth
                                    @php
                                        $canDeleteOwnComment =
                                            (int) $comment->user_id === (int) auth()->id();

                                        $hasAdminRole =
                                            method_exists(auth()->user(), 'hasRole')
                                            && auth()->user()->hasRole('ADMIN');

                                        $hasCommentDestroyRoute =
                                            \Illuminate\Support\Facades\Route::has('comments.destroy');
                                    @endphp

                                    @if (($canDeleteOwnComment || $hasAdminRole) && $hasCommentDestroyRoute)
                                        <form
                                            method="POST"
                                            action="{{ route('comments.destroy', $comment) }}"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa bình luận này không?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-xl border border-rose-400/20 bg-rose-500/15 px-3 py-2 text-xs font-semibold text-rose-100 hover:bg-rose-500/25 transition"
                                            >
                                                Xóa
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </div>

                            <div class="mt-4 rounded-3xl border border-white/10 bg-slate-950/60 p-5">
                                <p class="text-left text-slate-200 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-7">
                                    {{ $comment->content }}
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.js-comment-form');

        if (!form) {
            return;
        }

        const textarea = form.querySelector('textarea[name="content"]');
        const submitButton = form.querySelector('.js-comment-submit');
        const errorBox = form.querySelector('.js-comment-error');
        const successBox = document.getElementById('comment-success-message');

        function setLoading(isLoading) {
            if (!submitButton) {
                return;
            }

            submitButton.disabled = isLoading;
            submitButton.textContent = isLoading
                ? 'Đang gửi...'
                : 'Gửi bình luận';

            submitButton.classList.toggle('opacity-60', isLoading);
            submitButton.classList.toggle('cursor-not-allowed', isLoading);
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const content = textarea
                ? textarea.value.trim()
                : '';

            if (content.length < 2) {
                errorBox.textContent = 'Bình luận phải có ít nhất 2 ký tự.';
                errorBox.classList.remove('hidden');
                return;
            }

            errorBox.classList.add('hidden');
            errorBox.textContent = '';

            successBox.classList.add('hidden');
            successBox.textContent = '';

            const formData = new FormData(form);

            setLoading(true);

            try {
                const csrfToken = document.querySelector(
                    'meta[name="csrf-token"]'
                );

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                            ? csrfToken.getAttribute('content')
                            : '',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    let message =
                        data.message
                        || 'Không thể gửi bình luận. Vui lòng thử lại.';

                    if (data.errors?.content?.length) {
                        message = data.errors.content[0];
                    }

                    throw new Error(message);
                }

                textarea.value = '';

                successBox.textContent =
                    data.message
                    || 'Bình luận đã được gửi và đang chờ admin duyệt.';

                successBox.classList.remove('hidden');
            } catch (error) {
                errorBox.textContent =
                    error.message
                    || 'Không thể gửi bình luận. Vui lòng thử lại.';

                errorBox.classList.remove('hidden');
            } finally {
                setLoading(false);
            }
        });
    });
</script>