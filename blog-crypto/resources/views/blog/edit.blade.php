@section('title', 'Sửa bài viết - CryptoBlog')

<x-guest-layout>
    @php
        $categoryList = $categories ?? collect();
        $imageItems = $post->images ?? collect();
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl shadow-indigo-950/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-cyan-500/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06] bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] bg-[size:38px_38px]"></div>
            </div>

            <div class="relative p-6 md:p-10">
                <a href="{{ route('blog.my') }}"
                   class="inline-flex text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                    ← Quay lại Bài của tôi
                </a>

                <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-sm text-indigo-200">
                    <span class="h-2 w-2 rounded-full bg-indigo-300"></span>
                    Edit blog
                </div>

                <h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                    Sửa
                    <span class="bg-gradient-to-r from-indigo-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">
                        bài viết
                    </span>
                </h1>

                <p class="mt-4 max-w-2xl text-slate-300 leading-relaxed">
                    Cập nhật nội dung bằng trình soạn thảo trực quan và chèn ảnh minh họa ngay tại vị trí cần thiết trong bài.
                </p>
            </div>
        </section>

        @if ($errors->any())
            <section class="rounded-2xl bg-rose-400/10 border border-rose-400/20 px-5 py-4 text-rose-100">
                <div class="font-semibold mb-2">Có lỗi khi cập nhật bài:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="break-words [overflow-wrap:anywhere]">{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST"
              action="{{ route('blog.update', $post->id) }}"
              enctype="multipart/form-data"
              class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 md:p-8 space-y-7">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">
                    Tiêu đề bài viết
                </label>

                <input type="text"
                       name="title"
                       value="{{ old('title', $post->title) }}"
                       required
                       class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">
                    Chuyên mục
                </label>

                <select name="category_id"
                        class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30">
                    <option value="">Chưa phân loại</option>
                    @foreach ($categoryList as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>
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
                        Có thể giữ thumbnail cũ, nhập URL mới hoặc tải ảnh mới từ máy.
                    </p>
                </div>

                @if ($post->thumbnail)
                    <div class="rounded-3xl border border-white/10 bg-slate-950/60 overflow-hidden">
                        <img src="{{ $post->thumbnail }}"
                             alt="{{ $post->title }}"
                             class="h-56 w-full object-cover">

                        <label class="flex items-center gap-3 p-4 text-sm text-slate-300">
                            <input type="checkbox"
                                   name="remove_thumbnail"
                                   value="1"
                                   class="rounded border-white/10 bg-slate-900 text-rose-500 focus:ring-rose-400">
                            <span>Xóa thumbnail hiện tại</span>
                        </label>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">
                        URL thumbnail
                    </label>

                    <input type="text"
                           name="thumbnail"
                           value="{{ old('thumbnail', $post->thumbnail) }}"
                           class="w-full rounded-2xl bg-slate-950/70 border border-white/10 text-slate-100 px-4 py-3 focus:border-cyan-400 focus:ring-cyan-400/30"
                           placeholder="https://...">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">
                        Tải thumbnail mới từ máy
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
                        Bạn có thể định dạng nội dung và chèn ảnh trực tiếp tại vị trí con trỏ.
                        Ảnh mới không còn bị đưa cố định xuống cuối bài.
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
                >{{ old('content', $post->content) }}</textarea>
            </section>

            @if ($imageItems->count() > 0)
                <section class="rounded-3xl border border-amber-400/20 bg-amber-400/10 p-5 space-y-5">
                    <div>
                        <h2 class="text-xl font-black text-white">
                            Ảnh minh họa kiểu cũ
                        </h2>

                        <p class="mt-2 text-sm text-slate-300 leading-7">
                            Đây là các ảnh từng được tải theo cơ chế cũ và đang hiển thị ở cuối bài.
                            Bạn có thể giữ lại hoặc chọn xóa. Ảnh mới nên được chèn trực tiếp trong trình soạn thảo phía trên.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach ($imageItems as $image)
                            <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-950/60">
                                <img
                                    src="{{ $image->image_path }}"
                                    alt="{{ $image->original_name ?: 'Ảnh minh họa' }}"
                                    class="h-56 w-full object-cover"
                                >

                                <label class="flex items-center gap-3 p-4 text-sm text-slate-300">
                                    <input
                                        type="checkbox"
                                        name="delete_image_ids[]"
                                        value="{{ $image->id }}"
                                        class="rounded border-white/10 bg-slate-900 text-rose-500 focus:ring-rose-400"
                                    >
                                    <span>Xóa ảnh này</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="rounded-3xl border border-yellow-400/20 bg-yellow-400/10 p-5">
                <div class="text-sm font-bold text-yellow-200">
                    Lưu ý sau khi sửa bài
                </div>

                <p class="mt-2 text-sm text-slate-300 leading-7">
                    Nếu bạn sửa bài sau khi đã được duyệt, bài có thể quay lại trạng thái chờ admin kiểm duyệt để đảm bảo chất lượng nội dung.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-5 border-t border-white/10">
                <a href="{{ route('blog.my') }}"
                   class="rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10 transition">
                    Hủy
                </a>

                <button type="submit"
                        class="rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:from-indigo-400 hover:to-cyan-400 transition">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>