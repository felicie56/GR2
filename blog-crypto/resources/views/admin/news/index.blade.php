<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-white">Quản lý Tin tức</h1>
            <a href="{{ route('admin.news.create') }}"
               class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">
                + Thêm tin
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 text-green-400">{{ session('success') }}</div>
        @endif

        @if ($news->count() === 0)
            <p class="text-gray-300">Chưa có tin tức nào.</p>
        @else
            <div class="space-y-4">
                @foreach ($news as $item)
                    <div class="bg-slate-800/70 rounded-lg p-4 flex justify-between gap-4">
                        <div>
                            <div class="text-white font-semibold">{{ $item->title }}</div>
                            <div class="text-sm text-gray-400">
                                {{ $item->published_at?->format('d/m/Y H:i') ?? 'Chưa rõ thời gian' }}
                                @if($item->source) • {{ $item->source }} @endif
                            </div>
                            <div class="text-sm text-gray-500">Slug: {{ $item->slug }}</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('news.show', $item->slug) }}"
                               class="px-3 py-1 rounded bg-slate-700 hover:bg-slate-600 text-white text-sm">
                                Xem
                            </a>
                            <a href="{{ route('admin.news.edit', $item->id) }}"
                               class="px-3 py-1 rounded bg-yellow-600 hover:bg-yellow-700 text-white text-sm">
                                Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.news.destroy', $item->id) }}"
                                  onsubmit="return confirm('Xoá tin này?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-sm">
                                    Xoá
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $news->links() }}</div>
        @endif
    </div>
</x-guest-layout>
