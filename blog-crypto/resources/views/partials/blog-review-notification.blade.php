@auth
    @php
        $blogReviewNotification = auth()->user()
            ->blogPosts()
            ->with('reviewer')
            ->whereIn('status', ['approved', 'rejected'])
            ->whereNull('author_seen_at')
            ->latest('reviewed_at')
            ->first();
    @endphp

    @if ($blogReviewNotification)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
            <div class="w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl p-6">
                @if ($blogReviewNotification->status === 'approved')
                    <div class="text-green-300 text-sm font-semibold mb-2">
                        Bài viết đã được duyệt
                    </div>

                    <h2 class="text-2xl font-bold text-white">
                        Bài viết của bạn đã được công khai!
                    </h2>

                    <p class="mt-3 text-gray-300 leading-relaxed">
                        Bài viết
                        <span class="font-semibold text-white break-words [overflow-wrap:anywhere]">
                            “{{ $blogReviewNotification->title }}”
                        </span>
                        đã được admin phê duyệt và hiện đã xuất hiện trên trang Blog.
                    </p>

                    @if ($blogReviewNotification->reviewed_at)
                        <p class="mt-3 text-sm text-gray-400">
                            Thời gian duyệt:
                            {{ $blogReviewNotification->reviewed_at->format('d/m/Y H:i') }}
                        </p>
                    @endif

                    @if ($blogReviewNotification->reviewer)
                        <p class="mt-1 text-sm text-gray-400">
                            Người duyệt:
                            {{ $blogReviewNotification->reviewer->name }}
                        </p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('blog.show', $blogReviewNotification->slug) }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            Xem bài viết
                        </a>

                        <form method="POST" action="{{ route('blog.review.mark-seen', $blogReviewNotification) }}">
                            @csrf

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-600 text-gray-300 hover:bg-slate-800 font-semibold">
                                Đã hiểu
                            </button>
                        </form>
                    </div>
                @elseif ($blogReviewNotification->status === 'rejected')
                    <div class="text-red-300 text-sm font-semibold mb-2">
                        Bài viết chưa được duyệt
                    </div>

                    <h2 class="text-2xl font-bold text-white">
                        Bài viết của bạn đã bị từ chối
                    </h2>

                    <p class="mt-3 text-gray-300 leading-relaxed">
                        Bài viết
                        <span class="font-semibold text-white break-words [overflow-wrap:anywhere]">
                            “{{ $blogReviewNotification->title }}”
                        </span>
                        chưa đạt yêu cầu để công khai.
                    </p>

                    @if ($blogReviewNotification->rejection_reason)
                        <div class="mt-4 rounded-lg border border-red-700 bg-red-900/30 p-4">
                            <div class="text-sm font-semibold text-red-200">
                                Lý do từ chối:
                            </div>

                            <p class="mt-2 text-red-100 whitespace-pre-wrap break-words [overflow-wrap:anywhere]">
                                {{ $blogReviewNotification->rejection_reason }}
                            </p>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('blog.edit', $blogReviewNotification) }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            Sửa và gửi lại
                        </a>

                        <form method="POST" action="{{ route('blog.review.mark-seen', $blogReviewNotification) }}">
                            @csrf

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-600 text-gray-300 hover:bg-slate-800 font-semibold">
                                Đã hiểu
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endauth