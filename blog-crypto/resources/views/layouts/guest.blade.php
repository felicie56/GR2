<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'CryptoBlog'))</title>

<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        ::selection {
            background: rgba(34, 211, 238, 0.35);
            color: #ffffff;
        }

        .crypto-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .crypto-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.7);
        }

        .crypto-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(103, 232, 249, 0.35);
            border-radius: 999px;
        }

        .crypto-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(103, 232, 249, 0.55);
        }

        .article-content,
.article-content * {
    text-align: left !important;
    text-indent: 0 !important;
}

.article-content {
    width: 100%;
    max-width: none;
}

.article-content p {
    margin: 0 0 1.25rem 0;
    line-height: 1.9;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.article-content p:last-child {
    margin-bottom: 0;
}

.article-content a {
    color: rgb(103, 232, 249);
    text-decoration: underline;
    text-underline-offset: 3px;
}

textarea,
input,
select {
    text-align: left;
}

    </style>
</head>

@php
    $user = auth()->user();

    $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('ADMIN');
    $isAuthor = $user && method_exists($user, 'hasRole') && $user->hasRole('AUTHOR');

    $brandUrl = route('home');

    if ($isAdmin && \Illuminate\Support\Facades\Route::has('admin.dashboard')) {
        $brandUrl = route('admin.dashboard');
    }

    $navClass = function ($active = false) {
        return $active
            ? 'whitespace-nowrap rounded-2xl bg-indigo-500/20 px-3.5 py-2 text-sm font-bold text-white ring-1 ring-indigo-400/20'
            : 'whitespace-nowrap rounded-2xl px-3.5 py-2 text-sm font-semibold text-slate-300 hover:bg-white/[0.06] hover:text-white transition';
    };

    $mobileNavClass = function ($active = false) {
        return $active
            ? 'block rounded-2xl bg-indigo-500/20 px-4 py-3 text-sm font-bold text-white ring-1 ring-indigo-400/20'
            : 'block rounded-2xl px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/[0.06] hover:text-white transition';
    };
@endphp

<body class="min-h-screen bg-[#050816] text-slate-100 antialiased crypto-scrollbar">
    <div class="min-h-screen relative overflow-x-hidden">
        {{-- Background --}}
        <div class="fixed inset-0 -z-10">
            <div class="absolute inset-0 bg-[#050816]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.22),transparent_36%),radial-gradient(circle_at_top_right,rgba(34,211,238,0.18),transparent_34%),radial-gradient(circle_at_bottom,rgba(16,185,129,0.10),transparent_32%)]"></div>
            <div class="absolute inset-0 opacity-[0.07] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:42px_42px]"></div>
            <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-cyan-500/10 to-transparent"></div>
        </div>

        {{-- NAVBAR --}}
        <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="h-20 flex items-center justify-between gap-4">

                    {{-- BRAND --}}
                    <a href="{{ $brandUrl }}" class="flex items-center gap-3 shrink-0">
                        <div class="h-11 w-11 rounded-2xl border border-cyan-400/30 bg-cyan-400/10 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                            <svg viewBox="0 0 24 24" class="h-7 w-7 text-cyan-300" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 3L19 7V17L12 21L5 17V7L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M12 7L15.5 9V15L12 17L8.5 15V9L12 7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div class="leading-tight">
                            <div class="text-lg font-black text-white">
                                CryptoBlog
                            </div>
                            <div class="text-xs text-slate-500">
                                Insights • News • Markets
                            </div>
                        </div>
                    </a>

                    {{-- DESKTOP NAV --}}
                    <div class="hidden lg:flex flex-1 items-center justify-center gap-1 min-w-0">
                        @if (\Illuminate\Support\Facades\Route::has('blog.index'))
                            <a href="{{ route('blog.index') }}"
                               class="{{ $navClass(request()->routeIs('blog.index') || request()->routeIs('blog.show')) }}">
                                Blog
                            </a>
                        @endif

                        @if (\Illuminate\Support\Facades\Route::has('news.index'))
                            <a href="{{ route('news.index') }}"
                               class="{{ $navClass(request()->routeIs('news.*') && !request()->routeIs('admin.news.*')) }}">
                                Tin tức
                            </a>
                        @endif

                        @if (\Illuminate\Support\Facades\Route::has('crypto.index'))
                            <a href="{{ route('crypto.index') }}"
                               class="{{ $navClass(request()->routeIs('crypto.*')) }}">
                                Giá Crypto
                            </a>
                        @endif

                        @auth
                            @if ($isAdmin)
                                <div class="mx-2 h-7 w-px bg-white/10"></div>

                                @if (\Illuminate\Support\Facades\Route::has('admin.author-applications.index'))
                                    <a href="{{ route('admin.author-applications.index') }}"
                                       class="{{ $navClass(request()->routeIs('admin.author-applications.*')) }}">
                                        Tác giả
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('admin.blog.pending'))
                                    <a href="{{ route('admin.blog.pending') }}"
                                       class="{{ $navClass(request()->routeIs('admin.blog.*')) }}">
                                        Duyệt blog
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('admin.comments.index'))
                                    <a href="{{ route('admin.comments.index') }}"
                                       class="{{ $navClass(request()->routeIs('admin.comments.*')) }}">
                                        Bình luận
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('admin.news.index'))
                                    <a href="{{ route('admin.news.index') }}"
                                       class="{{ $navClass(request()->routeIs('admin.news.*')) }}">
                                        Tin tức
                                    </a>
                                @endif
                            @elseif ($isAuthor)
                                <div class="mx-2 h-7 w-px bg-white/10"></div>

                                @if (\Illuminate\Support\Facades\Route::has('blog.my'))
                                    <a href="{{ route('blog.my') }}"
                                       class="{{ $navClass(request()->routeIs('blog.my')) }}">
                                        Bài của tôi
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('blog.create'))
                                    <a href="{{ route('blog.create') }}"
                                       class="{{ $navClass(request()->routeIs('blog.create')) }}">
                                        Viết bài
                                    </a>
                                @endif
                            @else
                                <div class="mx-2 h-7 w-px bg-white/10"></div>

                                @if (\Illuminate\Support\Facades\Route::has('author.apply.create'))
                                    <a href="{{ route('author.apply.create') }}"
                                       class="{{ $navClass(request()->routeIs('author.apply.*')) }}">
                                        Đăng ký tác giả
                                    </a>
                                @endif
                            @endif
                        @endauth
                    </div>

                    {{-- RIGHT AREA --}}
                    <div class="hidden lg:flex items-center gap-3 shrink-0">
                        @auth
                            <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.04] px-3 py-2">
                                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-sm font-black text-white">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>

                                <div class="leading-tight max-w-[130px]">
                                    <div class="truncate text-sm font-bold text-white">
                                        {{ $user->name }}
                                    </div>

                                    <div class="truncate text-xs text-slate-500">
                                        @if ($isAdmin)
                                            Administrator
                                        @elseif ($isAuthor)
                                            Author
                                        @else
                                            User
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if (\Illuminate\Support\Facades\Route::has('profile.edit'))
                                <a href="{{ route('profile.edit') }}"
                                   class="{{ $navClass(request()->routeIs('profile.*')) }}">
                                    Hồ sơ
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit"
                                        class="whitespace-nowrap rounded-2xl px-3.5 py-2 text-sm font-bold text-rose-300 hover:bg-rose-500/10 hover:text-rose-200 transition">
                                    Đăng xuất
                                </button>
                            </form>
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('login'))
                                <a href="{{ route('login') }}"
                                   class="{{ $navClass(request()->routeIs('login')) }}">
                                    Đăng nhập
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="whitespace-nowrap rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                                    Đăng ký
                                </a>
                            @endif
                        @endauth
                    </div>

                    {{-- MOBILE BUTTON --}}
                    <button id="mobile-menu-button"
                            type="button"
                            class="lg:hidden inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] p-3 text-slate-200 hover:bg-white/10 transition"
                            aria-label="Open menu">
                        <svg id="mobile-menu-open-icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>

                        <svg id="mobile-menu-close-icon" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- MOBILE MENU --}}
                <div id="mobile-menu" class="hidden lg:hidden pb-5">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/95 p-3 shadow-2xl shadow-slate-950/40 space-y-2">
                        @if (\Illuminate\Support\Facades\Route::has('blog.index'))
                            <a href="{{ route('blog.index') }}"
                               class="{{ $mobileNavClass(request()->routeIs('blog.index') || request()->routeIs('blog.show')) }}">
                                Blog
                            </a>
                        @endif

                        @if (\Illuminate\Support\Facades\Route::has('news.index'))
                            <a href="{{ route('news.index') }}"
                               class="{{ $mobileNavClass(request()->routeIs('news.*') && !request()->routeIs('admin.news.*')) }}">
                                Tin tức
                            </a>
                        @endif

                        @if (\Illuminate\Support\Facades\Route::has('crypto.index'))
                            <a href="{{ route('crypto.index') }}"
                               class="{{ $mobileNavClass(request()->routeIs('crypto.*')) }}">
                                Giá Crypto
                            </a>
                        @endif

                        @auth
                            <div class="my-3 h-px bg-white/10"></div>

                            @if ($isAdmin)
                                @if (\Illuminate\Support\Facades\Route::has('admin.author-applications.index'))
                                    <a href="{{ route('admin.author-applications.index') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('admin.author-applications.*')) }}">
                                        Duyệt tác giả
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('admin.blog.pending'))
                                    <a href="{{ route('admin.blog.pending') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('admin.blog.*')) }}">
                                        Duyệt blog
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('admin.comments.index'))
                                    <a href="{{ route('admin.comments.index') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('admin.comments.*')) }}">
                                        Bình luận
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('admin.news.index'))
                                    <a href="{{ route('admin.news.index') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('admin.news.*')) }}">
                                        Tin tức
                                    </a>
                                @endif
                            @elseif ($isAuthor)
                                @if (\Illuminate\Support\Facades\Route::has('blog.my'))
                                    <a href="{{ route('blog.my') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('blog.my')) }}">
                                        Bài của tôi
                                    </a>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('blog.create'))
                                    <a href="{{ route('blog.create') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('blog.create')) }}">
                                        Viết bài
                                    </a>
                                @endif
                            @else
                                @if (\Illuminate\Support\Facades\Route::has('author.apply.create'))
                                    <a href="{{ route('author.apply.create') }}"
                                       class="{{ $mobileNavClass(request()->routeIs('author.apply.*')) }}">
                                        Đăng ký tác giả
                                    </a>
                                @endif
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('profile.edit'))
                                <a href="{{ route('profile.edit') }}"
                                   class="{{ $mobileNavClass(request()->routeIs('profile.*')) }}">
                                    Hồ sơ
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit"
                                        class="w-full text-left rounded-2xl px-4 py-3 text-sm font-bold text-rose-300 hover:bg-rose-500/10 hover:text-rose-200 transition">
                                    Đăng xuất
                                </button>
                            </form>
                        @else
                            <div class="my-3 h-px bg-white/10"></div>

                            @if (\Illuminate\Support\Facades\Route::has('login'))
                                <a href="{{ route('login') }}"
                                   class="{{ $mobileNavClass(request()->routeIs('login')) }}">
                                    Đăng nhập
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="block rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20">
                                    Đăng ký
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </nav>

            {{-- SUBBAR --}}
            <div class="hidden md:block border-t border-white/5">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.9)]"></span>
                        <span>CryptoBlog hỗ trợ đọc tin, quản lý bài viết, giá crypto và trợ lý chatbot.</span>
                    </div>

                    <div>
                        Modern fintech workspace
                        <span class="mx-2">•</span>
                        <span class="text-cyan-300 font-semibold">Beta UI</span>
                    </div>
                </div>
            </div>
        </header>

        {{-- MAIN --}}
        <main>
            {{ $slot }}
        </main>

        {{-- FOOTER --}}
        <footer class="mt-12 border-t border-white/10 bg-slate-950/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="text-sm font-black text-white">
                            CryptoBlog
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Nội dung chỉ mang tính tham khảo, không phải lời khuyên đầu tư.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                        <span>Laravel MVC</span>
                        <span>•</span>
                        <span>Blade UI</span>
                        <span>•</span>
                        <span>MySQL</span>
                        <span>•</span>
                        <span>Role-based system</span>
                    </div>
                </div>
            </div>
        </footer>

        {{-- CHATBOT --}}
        @if (\Illuminate\Support\Facades\Route::has('chatbot.ask'))
            @include('partials.chatbot-widget')
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            const openIcon = document.getElementById('mobile-menu-open-icon');
            const closeIcon = document.getElementById('mobile-menu-close-icon');

            if (!button || !menu || !openIcon || !closeIcon) {
                return;
            }

            button.addEventListener('click', function () {
                menu.classList.toggle('hidden');
                openIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html>