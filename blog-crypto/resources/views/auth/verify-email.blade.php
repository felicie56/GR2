<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            {{-- LEFT PANEL --}}
            <section class="lg:col-span-5 relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-emerald-950/20 min-h-[500px]">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
                </div>

                <div class="relative h-full p-6 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-sm text-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Email verification
                        </div>

                        <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                            Xác thực
                            <span class="bg-gradient-to-r from-emerald-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                                email
                            </span>
                        </h1>

                        <p class="mt-5 text-slate-300 leading-relaxed">
                            Hãy xác thực địa chỉ email để bảo vệ tài khoản và sử dụng đầy đủ các tính năng của hệ thống.
                        </p>
                    </div>

                    <div class="mt-8 rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                        <div class="text-sm font-bold text-emerald-200">
                            Sau khi xác thực
                        </div>

                        <ul class="mt-3 space-y-2 text-sm text-slate-300">
                            <li>• Tài khoản có độ tin cậy cao hơn.</li>
                            <li>• Có thể tiếp tục các thao tác yêu cầu email hợp lệ.</li>
                            <li>• Dễ dàng khôi phục tài khoản khi cần.</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- CONTENT --}}
            <section class="lg:col-span-7">
                <div class="h-full rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-slate-950/20 p-6 md:p-10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black text-white">
                                Kiểm tra email của bạn
                            </h2>

                            <p class="mt-2 text-sm text-slate-400">
                                Hệ thống đã gửi liên kết xác thực tới email đăng ký.
                            </p>
                        </div>

                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/20">
                            ✅
                        </div>
                    </div>

                    <div class="mt-8 rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                        <p class="text-slate-300 leading-7">
                            Trước khi tiếp tục, vui lòng kiểm tra hộp thư đến và bấm vào đường link xác thực email.
                            Nếu bạn không nhận được email, có thể yêu cầu hệ thống gửi lại.
                        </p>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="mt-6 rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                            Một liên kết xác thực mới đã được gửi tới địa chỉ email bạn đã đăng ký.
                        </div>
                    @endif

                    <div class="mt-8 flex flex-col sm:flex-row sm:items-center gap-3">
                        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                            @csrf

                            <button type="submit"
                                    class="w-full sm:w-auto rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-cyan-400 transition">
                                Gửi lại email xác thực
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                            @csrf

                            <button type="submit"
                                    class="w-full sm:w-auto rounded-2xl border border-white/10 bg-white/[0.04] px-6 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                                Đăng xuất
                            </button>
                        </form>
                    </div>

                    <div class="mt-8 rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5">
                        <div class="text-sm font-bold text-yellow-200">
                            Không thấy email?
                        </div>

                        <p class="mt-2 text-sm text-slate-300 leading-7">
                            Hãy kiểm tra mục Spam/Promotions hoặc đảm bảo email đăng ký là chính xác.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>