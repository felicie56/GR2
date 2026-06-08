<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            {{-- LEFT PANEL --}}
            <section class="lg:col-span-5 relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-cyan-950/20 min-h-[480px]">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
                </div>

                <div class="relative h-full p-6 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm text-cyan-200">
                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            Account recovery
                        </div>

                        <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                            Khôi phục
                            <span class="bg-gradient-to-r from-cyan-300 via-indigo-300 to-emerald-300 bg-clip-text text-transparent">
                                mật khẩu
                            </span>
                        </h1>

                        <p class="mt-5 text-slate-300 leading-relaxed">
                            Nhập email đã đăng ký. Hệ thống sẽ gửi liên kết đặt lại mật khẩu nếu email tồn tại trong hệ thống.
                        </p>
                    </div>

                    <div class="mt-8 rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5">
                        <div class="text-sm font-bold text-yellow-200">
                            Lưu ý bảo mật
                        </div>

                        <p class="mt-2 text-sm text-slate-300 leading-7">
                            Không chia sẻ email khôi phục, mã xác thực hoặc đường link đặt lại mật khẩu cho người khác.
                        </p>
                    </div>
                </div>
            </section>

            {{-- FORM --}}
            <section class="lg:col-span-7">
                <div class="h-full rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20 p-6 md:p-10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black text-white">
                                Quên mật khẩu
                            </h2>

                            <p class="mt-2 text-sm text-slate-400">
                                Vui lòng nhập email để nhận liên kết đặt lại mật khẩu.
                            </p>
                        </div>

                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/20">
                            ✉️
                        </div>
                    </div>

                    @if (session('status'))
                        <div class="mt-6 rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                            <div class="font-semibold mb-2">Có lỗi xảy ra:</div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-6">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-200 mb-2">
                                Email tài khoản
                            </label>

                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="you@example.com">

                            @error('email')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                            Gửi link đặt lại mật khẩu
                        </button>

                        <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 text-center">
                            <p class="text-sm text-slate-400">
                                Đã nhớ mật khẩu?
                            </p>

                            <a href="{{ route('login') }}"
                               class="mt-3 inline-flex items-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-3 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/15 transition">
                                Quay lại đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>