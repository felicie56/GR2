<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-gray-100">
    <div class="min-h-screen flex flex-col">

        {{-- NAVBAR --}}
        <nav class="bg-slate-900/90 border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-lg font-semibold text-white">
                        CryptoBlog
                    </a>

                    <a href="{{ route('blog.index') }}"
                       class="text-sm {{ request()->routeIs('blog.*') || request()->routeIs('home') ? 'text-white' : 'text-gray-300' }} hover:text-white">
                        Blog
                    </a>

                    <a href="{{ route('news.index') }}"
                       class="text-sm {{ request()->routeIs('news.*') ? 'text-white' : 'text-gray-300' }} hover:text-white">
                        Tin tức
                    </a>

                    <a href="{{ route('crypto.index') }}"
                       class="text-sm {{ request()->routeIs('crypto.*') ? 'text-white' : 'text-gray-300' }} hover:text-white">
                        Giá Crypto
                    </a>

                    {{-- Chỉ ADMIN mới thấy --}}
                    @auth
                        @if (auth()->user()->hasRole('ADMIN'))
                            <a href="{{ route('admin.blog.pending') }}"
                               class="text-sm {{ request()->routeIs('admin.blog.*') ? 'text-white' : 'text-gray-300' }} hover:text-white">
                                Phê duyệt blog
                            </a>

                            <a href="{{ route('admin.news.create') }}"
                               class="text-sm {{ request()->routeIs('admin.news.create') ? 'text-white' : 'text-gray-300' }} hover:text-white">
                                Tạo tin tức
                            </a>
                        @endif
                    @endauth
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-sm text-gray-300 hidden sm:inline">
                            Xin chào, {{ Auth::user()->name }}
                        </span>

                        <a href="{{ route('dashboard') }}"
                           class="text-sm text-gray-300 hover:text-white">
                            Dashboard
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="text-sm text-red-400 hover:text-red-300">
                                Đăng xuất
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-sm text-gray-300 hover:text-white">
                            Đăng nhập
                        </a>
                        <a href="{{ route('register') }}"
                           class="text-sm text-blue-400 hover:text-blue-300">
                            Đăng ký
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        {{-- NỘI DUNG CHÍNH --}}
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {{ $slot }}
            </div>
        </main>

    </div>
</body>
</html>
