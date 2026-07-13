@section('title', 'Viết bài mới - CryptoBlog')

<x-guest-layout>
    @php
        $categoryList = $categories ?? collect();
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-cyan-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative p-6 md:p-10">
                <a href="{{ route('blog.my') }}"
                   class="inline-flex text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                    ← Quay lại Bài của tôi
                </a>

                <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-sm text-cyan-200">
                    <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                    Author workspace
                </div>

                <h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                    Viết bài
                    <span class="bg-gradient-to-r from-cyan-300 via-indigo-300 to-emerald-300 bg-clip-text text-transparent">
                        mới
                    </span>
                </h1>

                <p class="mt-4 max-w-2xl text-slate-300 leading-relaxed">
                    Tạo bài viết crypto với trình soạn thảo trực quan. Bạn có thể định dạng tiêu đề, bôi đậm, in nghiêng, trích dẫn và chèn ảnh ngay giữa các đoạn nội dung.
                </p>
            </div>
        </section>

        @if ($errors->any())
            <section class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                <div class="font-semibold mb-2">Có lỗi khi tạo bài:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST"
              action="{{ route('blog.store') }}"
              enctype="multipart/form-data"
              class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 md:p-8 space-y-7">
            @csrf

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">
                    Tiêu đề bài viết
                </label>

                <input type="text"
                       name="title"
                       value="{{ old('title') }}"
                       required
                       class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                       placeholder="VD: Bitcoin ETF ảnh hưởng gì tới thị trường crypto?">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">
                    Chuyên mục
                </label>

                <select name="category_id"
                        class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">
                    <option value="">Chưa phân loại</option>
                    @foreach ($categoryList as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <section class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 space-y-5">
                <div>
                    <h2 class="text-xl font-black text-white">
                        Ảnh đại diện bài viết
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Có thể nhập URL ảnh như trước hoặc tải ảnh thumbnail từ máy.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">
                        URL thumbnail
                    </label>

                    <input type="text"
                           name="thumbnail"
                           value="{{ old('thumbnail') }}"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                           placeholder="https://...">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">
                        Hoặc tải thumbnail từ máy
                    </label>

                    <input type="file"
                           name="thumbnail_file"
                           accept="image/jpeg,image/png,image/webp"
                           class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-2xl file:border-0 file:bg-cyan-500 file:px-5 file:py-3 file:text-sm file:font-bold file:text-white hover:file:bg-cyan-400">
                </div>
            </section>

            <section class="rounded-3xl border border-cyan-400/20 bg-cyan-400/10 p-5 md:p-6 space-y-5">
                <div>
                    <h2 class="text-xl font-black text-white">
                        Nội dung bài viết
                    </h2>

                    <p class="mt-2 text-sm text-slate-300 leading-7">
                        Dùng thanh công cụ để tạo đề mục, bôi đậm, in nghiêng, danh sách, trích dẫn và liên kết.
                        Khi bấm biểu tượng ảnh hoặc kéo thả ảnh vào vùng soạn thảo, ảnh sẽ được chèn đúng tại vị trí con trỏ.
                    </p>
                </div>

                <textarea
                    id="blog-content-editor"
                    name="content"
                    rows="18"
                    required
                    data-rich-text-editor
                    data-upload-url="{{ route('editor.images.upload') }}"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                    placeholder="Nhập nội dung bài viết..."
                >{{ old('content') }}</textarea>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-slate-300">
                        <span class="font-bold text-white">Định dạng:</span> đề mục, đậm, nghiêng, gạch chân.
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-slate-300">
                        <span class="font-bold text-white">Cấu trúc:</span> danh sách và trích dẫn.
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-slate-300">
                        <span class="font-bold text-white">Hình ảnh:</span> chèn trực tiếp giữa các đoạn.
                    </div>
                </div>
            </section>

            <div class="rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5">
                <div class="text-sm font-bold text-yellow-200">
                    Lưu ý kiểm duyệt
                </div>

                <p class="mt-2 text-sm text-slate-300 leading-7">
                    Sau khi gửi, bài viết sẽ ở trạng thái chờ admin duyệt. Chỉ bài được duyệt mới hiển thị công khai trên trang Blog.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-5 border-t border-white/10">
                <a href="{{ route('blog.my') }}"
                   class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                    Hủy
                </a>

                <button type="submit"
                        class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                    Gửi bài chờ duyệt
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>