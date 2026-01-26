<x-guest-layout>
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-white">Viết bài blog</h1>

        <form method="POST" action="{{ route('blog.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-gray-200">Tiêu đề</label>
                <input name="title" value="{{ old('title') }}"
                       class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-2" />
                @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-gray-200">Thumbnail (URL, có thể bỏ trống)</label>
                <input name="thumbnail" value="{{ old('thumbnail') }}"
                       class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-2" />
                @error('thumbnail') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-gray-200">Nội dung</label>
                <textarea name="content" rows="8"
                          class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-3"
                          placeholder="Nhập nội dung...">{{ old('content') }}</textarea>
                @error('content') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                Gửi bài (chờ duyệt)
            </button>
        </form>
    </div>
</x-guest-layout>
