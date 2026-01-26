<x-guest-layout>
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-white">Sửa tin tức</h1>

        <form method="POST" action="{{ route('admin.news.update', $article->id) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="text-gray-200">Tiêu đề</label>
                <input name="title" value="{{ old('title', $article->title) }}"
                       class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-2">
                @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-gray-200">Thumbnail (URL)</label>
                <input name="thumbnail" value="{{ old('thumbnail', $article->thumbnail) }}"
                       class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-2">
                @error('thumbnail') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-gray-200">Nguồn (source)</label>
                <input name="source" value="{{ old('source', $article->source) }}"
                       class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-2">
                @error('source') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-gray-200">Published at</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', optional($article->published_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-2">
                @error('published_at') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-gray-200">Nội dung (có thể dùng HTML)</label>
                <textarea name="content" rows="10"
                          class="w-full mt-1 rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-3">{{ old('content', $article->content) }}</textarea>
                @error('content') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded">
                Lưu thay đổi
            </button>
        </form>
    </div>
</x-guest-layout>
