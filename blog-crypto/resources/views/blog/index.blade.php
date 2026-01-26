<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-white">Blog Crypto</h1>

        @if ($posts->count() === 0)
            <p class="text-gray-300">Hiện chưa có bài viết nào.</p>
        @else
            <div class="space-y-6">
                @foreach ($posts as $post)
                    <article class="bg-slate-800/70 rounded-lg p-5 shadow">
                        <h2 class="text-2xl font-semibold mb-2">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-400 hover:underline">
                                {{ $post->title }}
                            </a>
                        </h2>

                        <p class="text-sm text-gray-400 mb-2">
                            Bởi {{ $post->author->name ?? 'Ẩn danh' }}
                            • {{ $post->created_at->format('d/m/Y H:i') }}
                        </p>

                        <p class="text-gray-200">
                            {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 200) }}
                        </p>
                        <p class="text-sm text-gray-400 mb-1">
    👍 {{ $post->reactions()->count() }} lượt thích
</p>

                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-guest-layout>
