<x-guest-layout>
    @php
        $latestApplication = $latestApplication ?? $application ?? null;

        $selectedAreas = old('expertise_areas', $latestApplication->expertise_areas ?? []);
        if (! is_array($selectedAreas)) {
            $selectedAreas = [];
        }

        $areaOptions = [
            'Bitcoin',
            'Ethereum',
            'Altcoin',
            'DeFi',
            'Stablecoin',
            'NFT / GameFi',
            'Blockchain technology',
            'Crypto security',
            'Risk management',
            'Market analysis',
            'Personal finance',
            'Regulation / Legal',
        ];

        $statusConfig = [
            'pending' => [
                'label' => 'Đang chờ duyệt',
                'class' => 'bg-yellow-400/10 text-yellow-200 border-yellow-400/20',
                'dot' => 'bg-yellow-300',
                'message' => 'Đơn của bạn đang được admin xem xét. Trong thời gian này bạn chưa có quyền đăng bài.',
            ],
            'approved' => [
                'label' => 'Đã được duyệt',
                'class' => 'bg-emerald-400/10 text-emerald-200 border-emerald-400/20',
                'dot' => 'bg-emerald-300',
                'message' => 'Bạn đã được cấp quyền AUTHOR và có thể bắt đầu viết bài.',
            ],
            'rejected' => [
                'label' => 'Đã bị từ chối',
                'class' => 'bg-rose-400/10 text-rose-200 border-rose-400/20',
                'dot' => 'bg-rose-300',
                'message' => 'Bạn có thể chỉnh sửa hồ sơ và gửi lại đơn đăng ký.',
            ],
        ];

        $badge = $latestApplication
            ? ($statusConfig[$latestApplication->status] ?? [
                'label' => strtoupper((string) $latestApplication->status),
                'class' => 'bg-slate-400/10 text-slate-200 border-slate-400/20',
                'dot' => 'bg-slate-300',
                'message' => 'Trạng thái đơn chưa xác định.',
            ])
            : null;
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-cyan-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
                <div class="lg:col-span-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm text-cyan-200">
                        <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_14px_rgba(103,232,249,0.9)]"></span>
                        Author application
                    </div>

                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        Đăng ký trở thành
                        <span class="bg-gradient-to-r from-cyan-300 via-indigo-300 to-emerald-300 bg-clip-text text-transparent">
                            tác giả
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-slate-300 leading-relaxed">
                        Hoàn thiện hồ sơ chuyên môn để admin đánh giá kinh nghiệm, độ tin cậy và khả năng viết nội dung crypto chất lượng trước khi cấp quyền AUTHOR.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if (auth()->user()->hasRole('AUTHOR'))
                            <a href="{{ route('blog.create') }}"
                               class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                                Viết bài mới
                            </a>

                            <a href="{{ route('blog.my') }}"
                               class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                                Bài của tôi
                            </a>
                        @else
                            <a href="#application-form"
                               class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                                Hoàn thiện hồ sơ
                            </a>
                        @endif

                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                            Xem Blog
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-400">Trạng thái hiện tại</div>

                                <div class="mt-2">
                                    @if ($latestApplication)
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-sm font-bold {{ $badge['class'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                                            {{ $badge['label'] }}
                                        </span>
                                    @elseif (auth()->user()->hasRole('AUTHOR'))
                                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-sm font-bold text-emerald-200">
                                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                            Đã là AUTHOR
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-400/20 bg-slate-400/10 px-3 py-1 text-sm font-bold text-slate-200">
                                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                            Chưa gửi đơn
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 text-2xl">
                                ✍️
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                            <div class="text-sm font-semibold text-cyan-200">
                                Tiêu chí xét duyệt
                            </div>

                            <ul class="mt-2 space-y-1 text-sm text-slate-300">
                                <li>• Có kinh nghiệm hoặc hiểu biết crypto.</li>
                                <li>• Hồ sơ xác thực rõ ràng.</li>
                                <li>• Bài viết mẫu có chất lượng.</li>
                                <li>• Không có dấu hiệu quảng cáo/scam.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FLASH / ERROR --}}
        <section class="space-y-3">
            @if (session('success'))
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 px-5 py-4 text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="rounded-2xl bg-yellow-400/10 border border-yellow-400/20 px-5 py-4 text-yellow-100">
                    {{ session('warning') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                    <div class="font-semibold mb-2">Có lỗi khi gửi đơn:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- CURRENT APPLICATION STATUS --}}
        @if ($latestApplication)
            <section class="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                    <div>
                        <h2 class="text-xl font-black text-white">
                            Đơn đăng ký gần nhất
                        </h2>

                        <p class="mt-2 text-slate-400">
                            {{ $badge['message'] }}
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-bold {{ $badge['class'] }}">
                        <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
                        {{ $badge['label'] }}
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Tên đăng ký</div>
                        <div class="mt-1 text-sm font-semibold text-white break-words [overflow-wrap:anywhere]">
                            {{ $latestApplication->full_name }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Kinh nghiệm</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            {{ $latestApplication->experience_years ?? 0 }} năm
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="text-xs text-slate-500">Thời gian gửi</div>
                        <div class="mt-1 text-sm font-semibold text-white">
                            {{ $latestApplication->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                @if ($latestApplication->status === 'rejected' && $latestApplication->rejection_reason)
                    <div class="mt-6 rounded-2xl border border-rose-400/20 bg-rose-400/10 p-5">
                        <div class="font-semibold text-rose-200">
                            Lý do từ chối:
                        </div>

                        <p class="mt-2 text-rose-100 whitespace-pre-wrap break-words [overflow-wrap:anywhere] leading-7">
                            {{ $latestApplication->rejection_reason }}
                        </p>
                    </div>
                @endif
            </section>
        @endif

        {{-- FORM --}}
        @if (! auth()->user()->hasRole('AUTHOR'))
            <form id="application-form"
                  method="POST"
                  action="{{ route('author.apply.store') }}"
                  class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 md:p-8 space-y-8">
                @csrf

                <div>
                    <h2 class="text-2xl font-black text-white">
                        Hồ sơ ứng tuyển tác giả
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Vui lòng nhập thông tin rõ ràng, chuyên nghiệp. Đây là cơ sở để admin đánh giá và cấp quyền AUTHOR.
                    </p>
                </div>

                {{-- Identity --}}
                <section class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            Họ tên thật
                        </label>

                        <input type="text"
                               name="full_name"
                               value="{{ old('full_name', $latestApplication->full_name ?? auth()->user()->name) }}"
                               required
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                               placeholder="VD: Nguyễn Văn A">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            Tên công khai / bút danh
                        </label>

                        <input type="text"
                               name="public_name"
                               value="{{ old('public_name', $latestApplication->public_name ?? auth()->user()->name) }}"
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                               placeholder="VD: Crypto Researcher">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            Headline chuyên môn
                        </label>

                        <input type="text"
                               name="headline"
                               value="{{ old('headline', $latestApplication->headline ?? '') }}"
                               required
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                               placeholder="VD: Blockchain researcher với kinh nghiệm phân tích DeFi và quản trị rủi ro">
                    </div>
                </section>

                {{-- Expertise --}}
                <section class="rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                    <h3 class="text-lg font-black text-white">
                        Chuyên môn & kinh nghiệm
                    </h3>

                    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-200 mb-2">
                                Số năm kinh nghiệm
                            </label>

                            <input type="number"
                                   min="0"
                                   max="50"
                                   name="experience_years"
                                   value="{{ old('experience_years', $latestApplication->experience_years ?? 0) }}"
                                   required
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-200 mb-2">
                                Lĩnh vực chuyên môn
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($areaOptions as $area)
                                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 hover:bg-white/[0.04]">
                                        <input type="checkbox"
                                               name="expertise_areas[]"
                                               value="{{ $area }}"
                                               @checked(in_array($area, $selectedAreas, true))
                                               class="rounded border-white/10 bg-slate-900 text-cyan-500 focus:ring-cyan-400">
                                        <span>{{ $area }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Links --}}
                <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            Website cá nhân
                        </label>

                        <input type="url"
                               name="website_url"
                               value="{{ old('website_url', $latestApplication->website_url ?? '') }}"
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                               placeholder="https://...">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            LinkedIn
                        </label>

                        <input type="url"
                               name="linkedin_url"
                               value="{{ old('linkedin_url', $latestApplication->linkedin_url ?? '') }}"
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                               placeholder="https://linkedin.com/in/...">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            X / Twitter
                        </label>

                        <input type="url"
                               name="x_url"
                               value="{{ old('x_url', $latestApplication->x_url ?? '') }}"
                               class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                               placeholder="https://x.com/...">
                    </div>
                </section>

                {{-- Text fields --}}
                <section class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            Tóm tắt kinh nghiệm
                        </label>

                        <textarea name="experience_summary"
                                  rows="8"
                                  required
                                  class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                  placeholder="Mô tả kinh nghiệm của bạn với crypto, tài chính, viết nội dung, nghiên cứu thị trường...">{{ old('experience_summary', $latestApplication->experience_summary ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-2">
                            Lý do muốn trở thành tác giả
                        </label>

                        <textarea name="motivation"
                                  rows="8"
                                  required
                                  class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                  placeholder="Bạn muốn đóng góp gì cho cộng đồng người đọc của website?">{{ old('motivation', $latestApplication->motivation ?? '') }}</textarea>
                    </div>
                </section>

                {{-- Sample article --}}
                <section class="rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                    <h3 class="text-lg font-black text-white">
                        Bài viết mẫu
                    </h3>

                    <p class="mt-2 text-sm text-slate-400">
                        Hãy gửi một bài viết mẫu để admin đánh giá phong cách viết, độ hiểu biết và tính an toàn của nội dung.
                    </p>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-200 mb-2">
                                Tiêu đề bài viết mẫu
                            </label>

                            <input type="text"
                                   name="sample_article_title"
                                   value="{{ old('sample_article_title', $latestApplication->sample_article_title ?? '') }}"
                                   required
                                   class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                   placeholder="VD: Stablecoin mất peg là gì và nhà đầu tư cần chú ý điều gì?">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-200 mb-2">
                                Nội dung bài viết mẫu
                            </label>

                            <textarea name="sample_article_content"
                                      rows="12"
                                      required
                                      class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                                      placeholder="Nhập nội dung bài viết mẫu...">{{ old('sample_article_content', $latestApplication->sample_article_content ?? '') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5 space-y-4">
                    <div>
                        <div class="text-sm font-bold text-yellow-200">
                            Cam kết nội dung
                        </div>

                        <p class="mt-2 text-sm text-slate-300 leading-7">
                            Trước khi gửi đơn, bạn cần xác nhận hai nội dung dưới đây để đảm bảo hồ sơ minh bạch và phù hợp với định hướng của website.
                        </p>
                    </div>

                    <label class="flex items-start gap-3 rounded-2xl border border-white/10 bg-slate-950/50 p-4 cursor-pointer">
                        <input type="checkbox"
                               name="truthful_information_confirmed"
                               value="1"
                               @checked(old('truthful_information_confirmed'))
                               class="mt-1 rounded border-white/10 bg-slate-900 text-cyan-500 focus:ring-cyan-400">

                        <span class="text-sm text-slate-300 leading-6">
                            Tôi xác nhận các thông tin trong hồ sơ là đúng sự thật và có thể được admin dùng để xét duyệt quyền tác giả.
                        </span>
                    </label>

                    @error('truthful_information_confirmed')
                        <p class="text-sm text-rose-300">{{ $message }}</p>
                    @enderror

                    <label class="flex items-start gap-3 rounded-2xl border border-white/10 bg-slate-950/50 p-4 cursor-pointer">
                        <input type="checkbox"
                               name="content_policy_confirmed"
                               value="1"
                               @checked(old('content_policy_confirmed'))
                               class="mt-1 rounded border-white/10 bg-slate-900 text-cyan-500 focus:ring-cyan-400">

                        <span class="text-sm text-slate-300 leading-6">
                            Tôi cam kết bài viết sau này không chứa nội dung lừa đảo, quảng cáo sai lệch, kêu gọi đầu tư rủi ro hoặc thông tin chưa kiểm chứng.
                        </span>
                    </label>

                    @error('content_policy_confirmed')
                        <p class="text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </section>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-5 border-t border-white/10">
                    <a href="{{ route('blog.index') }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                        Hủy
                    </a>

                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                        Gửi đơn đăng ký
                    </button>
                </div>
            </form>
        @else
            <section class="rounded-[2rem] border border-emerald-400/20 bg-emerald-400/10 p-8 text-center">
                <div class="mx-auto h-16 w-16 rounded-3xl bg-emerald-400/10 border border-emerald-400/20 flex items-center justify-center text-3xl">
                    ✅
                </div>

                <h2 class="mt-5 text-2xl font-black text-white">
                    Bạn đã là tác giả
                </h2>

                <p class="mt-2 text-slate-300">
                    Tài khoản của bạn đã được cấp quyền AUTHOR. Bạn có thể bắt đầu viết và quản lý bài viết của mình.
                </p>

                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('blog.create') }}"
                       class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white">
                        Viết bài mới
                    </a>

                    <a href="{{ route('blog.my') }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200">
                        Bài của tôi
                    </a>
                </div>
            </section>
        @endif
    </div>
</x-guest-layout>