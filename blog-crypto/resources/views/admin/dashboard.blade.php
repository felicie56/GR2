<x-guest-layout>
    @section('title', 'Admin Dashboard - CryptoBlog')
    @php
        $latestAuthorApplications = $latestAuthorApplications ?? collect();
        $latestPendingBlogPosts = $latestPendingBlogPosts ?? collect();
        $latestNews = $latestNews ?? collect();
        $latestUsers = $latestUsers ?? collect();

        $pendingTasks = ($pendingAuthorApplications ?? 0) + ($pendingBlogPosts ?? 0);

        $formatNumber = function ($value) {
            return number_format((int) ($value ?? 0));
        };

        $formatDateTime = function ($value) {
            if (! $value) {
                return 'Chưa có dữ liệu';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                return $value;
            }
        };

        $blogTotal = max((int) ($totalBlogPosts ?? 0), 1);
        $approvedBlogRate = round((($approvedBlogPosts ?? 0) / $blogTotal) * 100);
        $pendingBlogRate = round((($pendingBlogPosts ?? 0) / $blogTotal) * 100);
        $rejectedBlogRate = round((($rejectedBlogPosts ?? 0) / $blogTotal) * 100);

        $authorApplicationTotal = max(
            (int) (($pendingAuthorApplications ?? 0) + ($approvedAuthorApplications ?? 0) + ($rejectedAuthorApplications ?? 0)),
            1
        );

        $approvedAuthorRate = round((($approvedAuthorApplications ?? 0) / $authorApplicationTotal) * 100);
        $pendingAuthorRate = round((($pendingAuthorApplications ?? 0) / $authorApplicationTotal) * 100);
        $rejectedAuthorRate = round((($rejectedAuthorApplications ?? 0) / $authorApplicationTotal) * 100);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HERO / HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-indigo-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                        <span class="h-2 w-2 rounded-full bg-indigo-300 shadow-[0_0_14px_rgba(129,140,248,0.9)]"></span>
                        Admin control center
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Quản trị hệ thống
                        <span class="bg-gradient-to-r from-indigo-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">
                            CryptoBlog
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Theo dõi toàn bộ hoạt động của website: người dùng, tác giả, bài viết, tin tức, bình luận và dữ liệu thị trường crypto.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('admin.author-applications.index') }}"
                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                            Duyệt tác giả
                        </a>

                        <a href="{{ route('admin.blog.pending') }}"
                           class="inline-flex items-center rounded-2xl border border-yellow-400/20 bg-yellow-400/10 px-5 py-3 text-sm font-semibold text-yellow-100 hover:bg-yellow-400/15 transition">
                            Duyệt blog
                        </a>

                        <a href="{{ route('admin.news.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Quản lý tin tức
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Tác vụ cần xử lý</div>
                                <div class="mt-1 text-4xl font-black text-white">{{ $formatNumber($pendingTasks) }}</div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12L10 17L20 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4 5H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.55"/>
                                    <path d="M4 19H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.55"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <a href="{{ route('admin.author-applications.index', ['status' => 'pending']) }}"
                               class="rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4 hover:bg-yellow-400/15 transition">
                                <div class="text-xs text-yellow-200">Đơn tác giả</div>
                                <div class="mt-1 text-2xl font-black text-white">{{ $formatNumber($pendingAuthorApplications ?? 0) }}</div>
                            </a>

                            <a href="{{ route('admin.blog.pending') }}"
                               class="rounded-2xl border border-orange-400/20 bg-orange-400/10 p-4 hover:bg-orange-400/15 transition">
                                <div class="text-xs text-orange-200">Blog chờ duyệt</div>
                                <div class="mt-1 text-2xl font-black text-white">{{ $formatNumber($pendingBlogPosts ?? 0) }}</div>
                            </a>
                        </div>

                        <div class="mt-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                            <div class="text-sm font-semibold text-emerald-200">
                                Trạng thái hệ thống
                            </div>
                            <p class="mt-1 text-sm text-slate-300">
                                @if ($pendingTasks > 0)
                                    Có {{ $pendingTasks }} tác vụ đang cần admin xử lý.
                                @else
                                    Không có tác vụ khẩn cấp. Hệ thống đang ổn định.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- PRIORITY ALERTS --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <a href="{{ route('admin.author-applications.index', ['status' => 'pending']) }}"
               class="group relative overflow-hidden rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-6 hover:bg-yellow-400/15 transition">
                <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-yellow-400/10 blur-2xl"></div>

                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-yellow-200 font-semibold">
                            Đơn tác giả chờ duyệt
                        </div>

                        <div class="mt-3 text-5xl font-black text-white">
                            {{ $formatNumber($pendingAuthorApplications ?? 0) }}
                        </div>

                        <p class="mt-3 text-sm text-slate-400 max-w-xl">
                            Người dùng đang chờ xét duyệt hồ sơ để được cấp quyền AUTHOR và bắt đầu đăng bài.
                        </p>
                    </div>

                    <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-3 py-2 text-yellow-100 text-sm font-semibold">
                        Review →
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.blog.pending') }}"
               class="group relative overflow-hidden rounded-3xl border border-orange-400/20 bg-orange-400/10 p-6 hover:bg-orange-400/15 transition">
                <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-orange-400/10 blur-2xl"></div>

                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-orange-200 font-semibold">
                            Blog đang chờ kiểm duyệt
                        </div>

                        <div class="mt-3 text-5xl font-black text-white">
                            {{ $formatNumber($pendingBlogPosts ?? 0) }}
                        </div>

                        <p class="mt-3 text-sm text-slate-400 max-w-xl">
                            Bài viết của Author cần được kiểm tra nội dung trước khi công khai trên trang Blog.
                        </p>
                    </div>

                    <div class="rounded-2xl bg-orange-400/10 border border-orange-400/20 px-3 py-2 text-orange-100 text-sm font-semibold">
                        Moderate →
                    </div>
                </div>
            </a>
        </section>

        {{-- STATISTIC CARDS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 hover:bg-white/[0.06] transition">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-400">Tổng người dùng</div>
                    <div class="h-10 w-10 rounded-2xl bg-cyan-400/10 text-cyan-200 flex items-center justify-center border border-cyan-400/20">👥</div>
                </div>
                <div class="mt-4 text-3xl font-black text-white">{{ $formatNumber($totalUsers ?? 0) }}</div>
                <div class="mt-2 text-xs text-slate-500">Bao gồm USER / AUTHOR / ADMIN</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 hover:bg-white/[0.06] transition">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-400">Tác giả</div>
                    <div class="h-10 w-10 rounded-2xl bg-indigo-400/10 text-indigo-200 flex items-center justify-center border border-indigo-400/20">✍️</div>
                </div>
                <div class="mt-4 text-3xl font-black text-white">{{ $formatNumber($totalAuthors ?? 0) }}</div>
                <div class="mt-2 text-xs text-slate-500">Người dùng đã được cấp AUTHOR</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 hover:bg-white/[0.06] transition">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-400">Admin</div>
                    <div class="h-10 w-10 rounded-2xl bg-emerald-400/10 text-emerald-200 flex items-center justify-center border border-emerald-400/20">🛡️</div>
                </div>
                <div class="mt-4 text-3xl font-black text-white">{{ $formatNumber($totalAdmins ?? 0) }}</div>
                <div class="mt-2 text-xs text-slate-500">Tài khoản có quyền quản trị</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 hover:bg-white/[0.06] transition">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-400">Bình luận</div>
                    <div class="h-10 w-10 rounded-2xl bg-blue-400/10 text-blue-200 flex items-center justify-center border border-blue-400/20">💬</div>
                </div>
                <div class="mt-4 text-3xl font-black text-white">{{ $formatNumber($totalComments ?? 0) }}</div>
                <div class="mt-2 text-xs text-slate-500">Tổng comment trong hệ thống</div>
            </div>
        </section>

        {{-- CONTENT STATISTICS --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Blog status --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Trạng thái Blog
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Kiểm soát vòng đời bài viết.
                        </p>
                    </div>
                    <a href="{{ route('admin.blog.pending') }}" class="text-sm text-cyan-300 hover:text-cyan-200">
                        Xem →
                    </a>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Đã public</span>
                            <span class="font-semibold text-emerald-300">{{ $formatNumber($approvedBlogPosts ?? 0) }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-400" style="width: {{ min($approvedBlogRate, 100) }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Chờ duyệt</span>
                            <span class="font-semibold text-yellow-300">{{ $formatNumber($pendingBlogPosts ?? 0) }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full bg-yellow-400" style="width: {{ min($pendingBlogRate, 100) }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Bị từ chối</span>
                            <span class="font-semibold text-rose-300">{{ $formatNumber($rejectedBlogPosts ?? 0) }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full bg-rose-400" style="width: {{ min($rejectedBlogRate, 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                    <div class="text-xs text-slate-500">Tổng bài viết</div>
                    <div class="mt-1 text-2xl font-black text-white">{{ $formatNumber($totalBlogPosts ?? 0) }}</div>
                </div>
            </div>

            {{-- Author applications --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Đơn đăng ký Author
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Theo dõi phê duyệt tác giả.
                        </p>
                    </div>
                    <a href="{{ route('admin.author-applications.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">
                        Xem →
                    </a>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Đã duyệt</span>
                            <span class="font-semibold text-emerald-300">{{ $formatNumber($approvedAuthorApplications ?? 0) }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-400" style="width: {{ min($approvedAuthorRate, 100) }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Chờ duyệt</span>
                            <span class="font-semibold text-yellow-300">{{ $formatNumber($pendingAuthorApplications ?? 0) }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full bg-yellow-400" style="width: {{ min($pendingAuthorRate, 100) }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Đã từ chối</span>
                            <span class="font-semibold text-rose-300">{{ $formatNumber($rejectedAuthorApplications ?? 0) }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full bg-rose-400" style="width: {{ min($rejectedAuthorRate, 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                    <div class="text-xs text-slate-500">Tổng đơn đã ghi nhận</div>
                    <div class="mt-1 text-2xl font-black text-white">{{ $formatNumber($authorApplicationTotal === 1 && (($pendingAuthorApplications ?? 0) + ($approvedAuthorApplications ?? 0) + ($rejectedAuthorApplications ?? 0)) === 0 ? 0 : $authorApplicationTotal) }}</div>
                </div>
            </div>

            {{-- Content & Crypto --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div>
                    <h2 class="text-lg font-bold text-white">
                        Nội dung & Crypto
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Tài nguyên đang có trong hệ thống.
                    </p>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <span class="text-slate-400">Tin tức</span>
                        <span class="text-white font-bold">{{ $formatNumber($totalNews ?? 0) }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <span class="text-slate-400">Chuyên mục</span>
                        <span class="text-white font-bold">{{ $formatNumber($totalCategories ?? 0) }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <span class="text-slate-400">Coin đang theo dõi</span>
                        <span class="text-white font-bold">{{ $formatNumber($totalCryptoCoins ?? 0) }}</span>
                    </div>

                    <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                        <div class="text-sm font-semibold text-cyan-200">Lần cập nhật giá gần nhất</div>
                        <div class="mt-1 text-sm text-slate-200 break-words">
                            {{ $formatDateTime($latestCryptoPriceAt ?? null) }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- RECENT DATA --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Latest author applications --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Đơn Author gần đây
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Hồ sơ tác giả mới gửi.
                        </p>
                    </div>

                    <a href="{{ route('admin.author-applications.index') }}"
                       class="text-sm text-cyan-300 hover:text-cyan-200 font-semibold">
                        Xem tất cả →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($latestAuthorApplications as $application)
                        <div class="rounded-2xl bg-slate-950/50 border border-white/10 p-4 hover:bg-slate-950/70 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-white break-words [overflow-wrap:anywhere]">
                                        {{ $application->full_name }}
                                    </div>

                                    <div class="text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                        {{ $application->user?->email ?? 'Không có email' }}
                                    </div>

                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $application->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>

                                @if ($application->status === 'pending')
                                    <span class="shrink-0 px-2.5 py-1 rounded-full text-xs bg-yellow-400/10 text-yellow-200 border border-yellow-400/20">
                                        Pending
                                    </span>
                                @elseif ($application->status === 'approved')
                                    <span class="shrink-0 px-2.5 py-1 rounded-full text-xs bg-emerald-400/10 text-emerald-200 border border-emerald-400/20">
                                        Approved
                                    </span>
                                @else
                                    <span class="shrink-0 px-2.5 py-1 rounded-full text-xs bg-rose-400/10 text-rose-200 border border-rose-400/20">
                                        Rejected
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('admin.author-applications.show', $application) }}"
                                   class="text-sm text-cyan-300 hover:text-cyan-200 font-semibold">
                                    Xem chi tiết →
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-5 text-slate-400">
                            Chưa có đơn đăng ký tác giả nào.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pending blogs --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Blog đang chờ duyệt
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Nội dung cần admin kiểm duyệt.
                        </p>
                    </div>

                    <a href="{{ route('admin.blog.pending') }}"
                       class="text-sm text-cyan-300 hover:text-cyan-200 font-semibold">
                        Xem tất cả →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($latestPendingBlogPosts as $post)
                        <div class="rounded-2xl bg-slate-950/50 border border-white/10 p-4 hover:bg-slate-950/70 transition">
                            <div class="font-semibold text-white break-words [overflow-wrap:anywhere]">
                                {{ $post->title }}
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-indigo-400/10 text-indigo-200 border border-indigo-400/20 px-2.5 py-1">
                                    {{ $post->category?->name ?? 'Chưa phân loại' }}
                                </span>

                                <span class="rounded-full bg-white/[0.04] text-slate-400 border border-white/10 px-2.5 py-1">
                                    {{ $post->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <div class="text-sm text-slate-400 mt-3">
                                Tác giả:
                                <span class="text-slate-200 font-semibold">
                                    {{ $post->author?->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-5 text-slate-400">
                            Không có bài blog nào đang chờ duyệt.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Latest news --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Tin tức mới nhất
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Các tin được cập nhật gần đây.
                        </p>
                    </div>

                    <a href="{{ route('admin.news.index') }}"
                       class="text-sm text-cyan-300 hover:text-cyan-200 font-semibold">
                        Quản lý →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($latestNews as $item)
                        <div class="rounded-2xl bg-slate-950/50 border border-white/10 p-4 hover:bg-slate-950/70 transition">
                            <div class="font-semibold text-white break-words [overflow-wrap:anywhere]">
                                {{ $item->title }}
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-blue-400/10 text-blue-200 border border-blue-400/20 px-2.5 py-1">
                                    {{ $item->category?->name ?? 'Chưa phân loại' }}
                                </span>

                                @if ($item->published_at)
                                    <span class="rounded-full bg-white/[0.04] text-slate-400 border border-white/10 px-2.5 py-1">
                                        {{ $item->published_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('news.show', $item->slug) }}"
                                   class="text-sm text-cyan-300 hover:text-cyan-200 font-semibold">
                                    Xem tin →
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-5 text-slate-400">
                            Chưa có tin tức nào.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Latest users --}}
            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Người dùng mới
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Các tài khoản đăng ký gần đây.
                        </p>
                    </div>

                    <div class="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 text-xs text-slate-400">
                        Latest
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($latestUsers as $user)
                        <div class="rounded-2xl bg-slate-950/50 border border-white/10 p-4 hover:bg-slate-950/70 transition">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm font-bold text-white">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <div class="font-semibold text-white truncate">
                                        {{ $user->name }}
                                    </div>

                                    <div class="text-sm text-slate-400 truncate">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-xs text-slate-500 mt-3">
                                Tham gia: {{ $user->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-5 text-slate-400">
                            Chưa có người dùng nào.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- QUICK ACTIONS --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-5 md:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-white">
                        Thao tác nhanh
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Các lối tắt quản trị thường dùng trong quá trình vận hành website.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.author-applications.index') }}"
                       class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        Duyệt tác giả
                    </a>

                    <a href="{{ route('admin.blog.pending') }}"
                       class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        Duyệt blog
                    </a>

                    <a href="{{ route('admin.comments.index') }}"
                       class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        Quản lý bình luận
                    </a>

                    <a href="{{ route('admin.news.index') }}"
                       class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        Quản lý tin tức
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>