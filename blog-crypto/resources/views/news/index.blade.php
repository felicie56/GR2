<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-white">Tin tức tài chính & Crypto</h1>

        @if ($news->count() === 0)
            <p class="text-gray-300">Hiện chưa có tin tức nào.</p>
        @else
            <div class="space-y-6">
                @foreach ($news as $item)
                    <article class="bg-slate-800/70 rounded-lg p-5 shadow">
                        <h2 class="text-2xl font-semibold mb-2">
                            <a href="{{ route('news.show', $item->slug) }}"
                               class="text-blue-400 hover:underline">
                                {{ $item->title }}
                            </a>
                        </h2>

                        <p class="text-sm text-gray-400 mb-2">
                            {{ $item->published_at?->format('d/m/Y H:i') ?? 'Chưa rõ thời gian' }}
                            @if($item->source)
                                • Nguồn: {{ $item->source }}
                            @endif
                        </p>

                        <p class="text-gray-200">
                            {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 200) }}
                        </p>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</x-guest-layout>
