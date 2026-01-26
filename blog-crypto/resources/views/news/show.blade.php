<x-guest-layout>
    <div class="max-w-3xl mx-auto py-8">
        <a href="{{ route('news.index') }}" class="text-blue-400 hover:underline">&larr; Quay lại danh sách tin</a>

        <h1 class="text-3xl font-bold mt-4 mb-2 text-white">
            {{ $article->title }}
        </h1>

        <p class="text-sm text-gray-400 mb-4">
            {{ $article->published_at?->format('d/m/Y H:i') ?? 'Chưa rõ thời gian' }}
            @if($article->source)
                • Nguồn: {{ $article->source }}
            @endif
        </p>

        @if ($article->thumbnail)
            <img src="{{ $article->thumbnail }}" alt="Thumbnail"
                 class="w-full max-h-96 object-cover rounded-lg mb-4">
        @endif

        <div class="prose prose-invert max-w-none mb-10">
            {!! $article->content !!}
        </div>

        {{-- ================== COMMENTS ================== --}}
        <section class="mt-8">
            <h2 class="text-xl font-semibold text-white mb-4">Bình luận</h2>

            @if (session('success'))
                <div class="mb-4 text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            @auth
                <form method="POST"
                      action="{{ route('news.comments.store', $article->id) }}"
                      class="mb-6">
                    @csrf
                    <textarea
                        name="content"
                        rows="3"
                        class="w-full rounded-md bg-slate-800 border border-slate-700 text-gray-100 p-3"
                        placeholder="Nhập bình luận của bạn...">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                        Gửi bình luận
                    </button>
                </form>
            @else
                <p class="mb-4 text-gray-300">
                    Bạn cần <a href="{{ route('login') }}" class="text-blue-400 hover:underline">đăng nhập</a> để bình luận.
                </p>
            @endauth

            <div class="space-y-4">
                @forelse ($article->comments as $comment)
                    <div class="bg-slate-800/60 rounded-md p-3">
                        <p class="text-sm text-gray-400 mb-1">
                            {{ $comment->user->name ?? 'Người dùng' }}
                            • {{ $comment->created_at->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-gray-100">
                            {{ $comment->content }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-300">Chưa có bình luận nào.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-guest-layout>
