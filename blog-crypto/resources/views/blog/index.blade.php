<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8">

        {{-- Header + nút viết blog --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-white">Blog Crypto</h1>

            @auth
                @if(auth()->user()->hasRole('AUTHOR'))
                    <a href="{{ route('blog.create') }}"
                       class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">
                        Viết blog
                    </a>
                @endif
            @endauth
        </div>

        {{-- Danh sách bài viết --}}
        @if ($posts->count() === 0)
            <p class="text-gray-300">Hiện chưa có bài viết nào.</p>
        @else
            <div class="space-y-6">
                @foreach ($posts as $post)
                    <article class="bg-slate-800/70 rounded-lg p-5 shadow">
                        <h2 class="text-2xl font-semibold mb-2">
                            <a href="{{ route('blog.show', $post->slug) }}"
                               class="text-blue-400 hover:underline">
                                {{ $post->title }}
                            </a>
                        </h2>

                        <p class="text-sm text-gray-400 mb-2">
                            Bởi {{ $post->author->name ?? 'Ẩn danh' }}
                            • {{ $post->created_at->format('d/m/Y H:i') }}
                        </p>

                        <p class="text-gray-200 mb-2">
                            {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 200) }}
                        </p>

                        <p class="text-sm text-gray-400">
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
