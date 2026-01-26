<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-white">Duyệt bài blog (Pending)</h1>

        @if (session('success'))
            <div class="mb-4 text-green-400">{{ session('success') }}</div>
        @endif

        @if ($posts->count() === 0)
            <p class="text-gray-300">Không có bài nào đang chờ duyệt.</p>
        @else
            <div class="space-y-4">
                @foreach ($posts as $post)
                    <div class="bg-slate-800/70 rounded-lg p-5">
                        <h2 class="text-xl font-semibold text-white">{{ $post->title }}</h2>
                        <p class="text-sm text-gray-400 mb-3">
                            Tác giả: {{ $post->author?->name ?? 'N/A' }}
                            • {{ $post->created_at->format('d/m/Y H:i') }}
                        </p>

                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.blog.approve', $post->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded">
                                    Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.blog.reject', $post->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $posts->links() }}</div>
        @endif
    </div>
</x-guest-layout>
