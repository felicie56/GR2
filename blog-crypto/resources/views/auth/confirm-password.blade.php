<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            {{-- LEFT PANEL --}}
            <section class="lg:col-span-5 relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-yellow-950/20 min-h-[460px]">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-yellow-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
                </div>

                <div class="relative h-full p-6 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-yellow-400/20 bg-yellow-400/10 px-3 py-1 text-sm text-yellow-200">
                            <span class="h-2 w-2 rounded-full bg-yellow-300"></span>
                            Security checkpoint
                        </div>

                        <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                            Xác nhận
                            <span class="bg-gradient-to-r from-yellow-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                                mật khẩu
                            </span>
                        </h1>

                        <p class="mt-5 text-slate-300 leading-relaxed">
                            Đây là khu vực bảo mật. Vui lòng nhập lại mật khẩu để xác nhận bạn là chủ tài khoản.
                        </p>
                    </div>

                    <div class="mt-8 rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5">
                        <div class="text-sm font-bold text-yellow-200">
                            Vì sao cần xác nhận?
                        </div>

                        <p class="mt-2 text-sm text-slate-300 leading-7">
                            Một số thao tác nhạy cảm yêu cầu xác nhận lại mật khẩu để bảo vệ tài khoản của bạn.
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
                                Xác nhận bảo mật
                            </h2>

                            <p class="mt-2 text-sm text-slate-400">
                                Nhập mật khẩu hiện tại để tiếp tục.
                            </p>
                        </div>

                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-yellow-400 to-indigo-500 flex items-center justify-center text-2xl shadow-lg shadow-yellow-500/20">
                            🛡️
                        </div>
                    </div>

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

                    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-6">
                        @csrf

                        <div>
                            <label for="password" class="block text-sm font-bold text-slate-200 mb-2">
                                Mật khẩu
                            </label>

                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-yellow-400 focus:ring-yellow-400/30"
                                   placeholder="••••••••">

                            @error('password')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-yellow-500 to-indigo-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-yellow-500/20 hover:from-yellow-400 hover:to-indigo-400 transition">
                            Xác nhận
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>