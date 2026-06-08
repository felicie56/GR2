<x-guest-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            {{-- LEFT BRAND PANEL --}}
            <section class="lg:col-span-6 relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-cyan-950/20 min-h-[560px]">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
                </div>

                <div class="relative h-full p-6 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm text-cyan-200">
                            <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_14px_rgba(103,232,249,0.9)]"></span>
                            Secure member access
                        </div>

                        <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                            Đăng nhập vào
                            <span class="bg-gradient-to-r from-cyan-300 via-indigo-300 to-emerald-300 bg-clip-text text-transparent">
                                CryptoBlog
                            </span>
                        </h1>

                        <p class="mt-5 max-w-xl text-slate-300 leading-relaxed">
                            Truy cập tài khoản để đọc nội dung, bình luận, gửi đơn đăng ký tác giả, quản lý bài viết và sử dụng các tính năng cá nhân hóa.
                        </p>
                    </div>

                    <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="text-2xl font-black text-white">USER</div>
                            <p class="mt-1 text-xs text-slate-400">Đọc bài, bình luận, tương tác.</p>
                        </div>

                        <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                            <div class="text-2xl font-black text-cyan-100">AUTHOR</div>
                            <p class="mt-1 text-xs text-slate-300">Viết và quản lý bài blog.</p>
                        </div>

                        <div class="rounded-2xl border border-indigo-400/20 bg-indigo-400/10 p-4">
                            <div class="text-2xl font-black text-indigo-100">ADMIN</div>
                            <p class="mt-1 text-xs text-slate-300">Duyệt và quản trị hệ thống.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- LOGIN FORM --}}
            <section class="lg:col-span-6">
                <div class="h-full rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20 p-6 md:p-10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black text-white">
                                Đăng nhập
                            </h2>

                            <p class="mt-2 text-sm text-slate-400">
                                Nhập thông tin tài khoản để tiếp tục sử dụng hệ thống.
                            </p>
                        </div>

                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/20">
                            🔐
                        </div>
                    </div>

                    {{-- Session Status --}}
                    @if (session('status'))
                        <div class="mt-6 rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Validation Summary --}}
                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                            <div class="font-semibold mb-2">Có lỗi khi đăng nhập:</div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-200 mb-2">
                                Email
                            </label>

                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="you@example.com">

                            @error('email')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-bold text-slate-200 mb-2">
                                Mật khẩu
                            </label>

                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="••••••••">

                            @error('password')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remember + Forgot --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-300">
                                <input id="remember_me"
                                       type="checkbox"
                                       name="remember"
                                       class="rounded border-white/10 bg-slate-900 text-cyan-500 focus:ring-cyan-400">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                                    Quên mật khẩu?
                                </a>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                            Đăng nhập
                        </button>

                        {{-- Register link --}}
                        <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 text-center">
                            <p class="text-sm text-slate-400">
                                Chưa có tài khoản?
                            </p>

                            <a href="{{ route('register') }}"
                               class="mt-3 inline-flex items-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-3 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                Tạo tài khoản mới
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>