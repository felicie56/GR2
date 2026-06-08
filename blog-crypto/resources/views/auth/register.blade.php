<x-guest-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            {{-- LEFT BRAND PANEL --}}
            <section class="lg:col-span-6 relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-indigo-950/20 min-h-[620px]">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
                </div>

                <div class="relative h-full p-6 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                            <span class="h-2 w-2 rounded-full bg-indigo-300 shadow-[0_0_14px_rgba(129,140,248,0.9)]"></span>
                            Join crypto community
                        </div>

                        <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                            Tạo tài khoản
                            <span class="bg-gradient-to-r from-indigo-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">
                                CryptoBlog
                            </span>
                        </h1>

                        <p class="mt-5 max-w-xl text-slate-300 leading-relaxed">
                            Đăng ký tài khoản để đọc nội dung, bình luận, lưu trải nghiệm cá nhân và có thể gửi hồ sơ đăng ký trở thành tác giả.
                        </p>
                    </div>

                    <div class="mt-10 rounded-3xl border border-cyan-400/20 bg-cyan-400/10 p-5">
                        <div class="text-sm font-bold text-cyan-200">
                            Sau khi đăng ký, bạn có thể:
                        </div>

                        <ul class="mt-3 space-y-2 text-sm text-slate-300">
                            <li>• Đọc blog và tin tức crypto.</li>
                            <li>• Bình luận và tương tác với bài viết.</li>
                            <li>• Gửi đơn đăng ký trở thành AUTHOR.</li>
                            <li>• Theo dõi giá crypto và sử dụng chatbot hỗ trợ.</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- REGISTER FORM --}}
            <section class="lg:col-span-6">
                <div class="h-full rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20 p-6 md:p-10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black text-white">
                                Đăng ký
                            </h2>

                            <p class="mt-2 text-sm text-slate-400">
                                Tạo tài khoản người dùng mới để bắt đầu sử dụng website.
                            </p>
                        </div>

                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/20">
                            🚀
                        </div>
                    </div>

                    {{-- Validation Summary --}}
                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                            <div class="font-semibold mb-2">Có lỗi khi đăng ký:</div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-bold text-slate-200 mb-2">
                                Tên hiển thị
                            </label>

                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus
                                   autocomplete="name"
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="VD: Nam Crypto">

                            @error('name')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

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
                                   autocomplete="new-password"
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="Tối thiểu 8 ký tự">

                            @error('password')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-slate-200 mb-2">
                                Xác nhận mật khẩu
                            </label>

                            <input id="password_confirmation"
                                   type="password"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="Nhập lại mật khẩu">

                            @error('password_confirmation')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Note --}}
                        <div class="rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5">
                            <div class="text-sm font-bold text-yellow-200">
                                Lưu ý tài khoản
                            </div>

                            <p class="mt-2 text-sm text-slate-300 leading-7">
                                Sau khi đăng ký, tài khoản mặc định là USER. Nếu muốn viết bài, bạn cần gửi đơn đăng ký làm tác giả và chờ admin phê duyệt.
                            </p>
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                            Tạo tài khoản
                        </button>

                        {{-- Login link --}}
                        <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 text-center">
                            <p class="text-sm text-slate-400">
                                Đã có tài khoản?
                            </p>

                            <a href="{{ route('login') }}"
                               class="mt-3 inline-flex items-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-3 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                Đăng nhập ngay
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>