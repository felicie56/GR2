<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-white">Bài viết của tôi</h1>

        @if (session('success'))
            <div class="mb-4 text-green-400">{{ session('success') }}</div>
        @endif

        @if ($posts->count() === 0)
            <p class="text-gray-300">Bạn chưa có bài viết nào.</p>
        @else
            <div class="space-y-4">
                @foreach ($posts as $post)
                    <div class="bg-slate-800/70 rounded-lg p-5">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-xl font-semibold text-white">{{ $post->title }}</h2>
                                <p class="text-sm text-gray-400">
                                    Trạng thái:
                                    <span class="font-semibold">
                                        {{ $post->status }}
                                    </span>
                                    • {{ $post->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            @if($post->status === 'approved')
                                <a class="text-blue-400 hover:underline" href="{{ route('blog.show', $post->slug) }}">Xem</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $posts->links() }}</div>
        @endif
    </div>
</x-guest-layout>
