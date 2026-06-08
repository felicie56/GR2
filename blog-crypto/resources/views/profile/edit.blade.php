<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-indigo-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                        <span class="h-2 w-2 rounded-full bg-indigo-300 shadow-[0_0_14px_rgba(129,140,248,0.9)]"></span>
                        Account settings
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Hồ sơ
                        <span class="bg-gradient-to-r from-indigo-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">
                            cá nhân
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Quản lý thông tin tài khoản, bảo mật mật khẩu và các thiết lập cơ bản của người dùng trong hệ thống.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Xem Blog
                        </a>

                        @if (auth()->user()->hasRole('AUTHOR'))
                            <a href="{{ route('blog.my') }}"
                               class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                                Bài của tôi
                            </a>
                        @else
                            <a href="{{ route('author.apply.create') }}"
                               class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                                Đăng ký tác giả
                            </a>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center gap-4">
                            <div class="h-16 w-16 rounded-3xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-2xl font-black text-white shadow-lg shadow-cyan-500/20">
                                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="font-black text-white break-words [overflow-wrap:anywhere]">
                                    {{ auth()->user()->name }}
                                </div>

                                <div class="mt-1 text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                    {{ auth()->user()->email }}
                                </div>

                                <div class="mt-2">
                                    @if (auth()->user()->hasRole('ADMIN'))
                                        <span class="rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-xs font-bold text-indigo-200">
                                            ADMIN
                                        </span>
                                    @elseif (auth()->user()->hasRole('AUTHOR'))
                                        <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-200">
                                            AUTHOR
                                        </span>
                                    @else
                                        <span class="rounded-full border border-slate-400/20 bg-slate-400/10 px-3 py-1 text-xs font-bold text-slate-200">
                                            USER
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                            <div class="text-sm font-semibold text-cyan-200">
                                Bảo mật tài khoản
                            </div>

                            <p class="mt-1 text-sm text-slate-300">
                                Hãy dùng mật khẩu mạnh và cập nhật thông tin email chính xác để bảo vệ tài khoản.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FLASH --}}
        <section class="space-y-3">
            @if (session('status') === 'profile-updated')
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                    Hồ sơ đã được cập nhật thành công.
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                    Mật khẩu đã được cập nhật thành công.
                </div>
            @endif
        </section>

        {{-- UPDATE PROFILE --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 md:p-8">
            <div>
                <h2 class="text-2xl font-black text-white">
                    Thông tin tài khoản
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Cập nhật tên hiển thị và địa chỉ email của bạn.
                </p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-bold text-slate-200 mb-2">
                        Tên hiển thị
                    </label>

                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name', auth()->user()->name) }}"
                           required
                           autofocus
                           autocomplete="name"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">

                    @error('name')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-200 mb-2">
                        Email
                    </label>

                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email', auth()->user()->email) }}"
                           required
                           autocomplete="username"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">

                    @error('email')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div class="rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4 text-yellow-100">
                        Email của bạn chưa được xác thực.

                        <button form="send-verification"
                                class="underline text-yellow-200 hover:text-yellow-100">
                            Gửi lại email xác thực
                        </button>
                    </div>
                @endif

                <div class="pt-4">
                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                        Lưu hồ sơ
                    </button>
                </div>
            </form>

            <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
                @csrf
            </form>
        </section>

        {{-- UPDATE PASSWORD --}}
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 md:p-8">
            <div>
                <h2 class="text-2xl font-black text-white">
                    Cập nhật mật khẩu
                </h2>

                <p class="mt-2 text-sm text-slate-400">
                    Sử dụng mật khẩu dài, khó đoán và không dùng lại mật khẩu ở nhiều nơi.
                </p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-bold text-slate-200 mb-2">
                        Mật khẩu hiện tại
                    </label>

                    <input id="current_password"
                           name="current_password"
                           type="password"
                           autocomplete="current-password"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">

                    @error('current_password', 'updatePassword')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-slate-200 mb-2">
                        Mật khẩu mới
                    </label>

                    <input id="password"
                           name="password"
                           type="password"
                           autocomplete="new-password"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">

                    @error('password', 'updatePassword')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-200 mb-2">
                        Xác nhận mật khẩu mới
                    </label>

                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           autocomplete="new-password"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">

                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
        </section>

        {{-- DELETE ACCOUNT --}}
        <section class="rounded-[2rem] border border-rose-400/20 bg-rose-400/10 p-6 md:p-8">
            <div>
                <h2 class="text-2xl font-black text-white">
                    Xóa tài khoản
                </h2>

                <p class="mt-2 text-sm text-rose-100/90">
                    Khi xóa tài khoản, dữ liệu liên quan có thể bị ảnh hưởng. Hãy chắc chắn trước khi thực hiện thao tác này.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('profile.destroy') }}"
                  class="mt-6 space-y-5"
                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password" class="block text-sm font-bold text-rose-100 mb-2">
                        Nhập mật khẩu để xác nhận
                    </label>

                    <input id="delete_password"
                           name="password"
                           type="password"
                           autocomplete="current-password"
                           class="w-full rounded-2xl bg-slate-950/70 border border-rose-400/20 text-slate-100 px-4 py-3 focus:border-rose-400 focus:ring-rose-400/30">

                    @error('password', 'userDeletion')
                        <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="rounded-2xl border border-rose-400/20 bg-rose-500/20 px-6 py-3 text-sm font-bold text-rose-100 hover:bg-rose-500/30 transition">
                    Xóa tài khoản
                </button>
            </form>
        </section>
    </div>
</x-guest-layout>