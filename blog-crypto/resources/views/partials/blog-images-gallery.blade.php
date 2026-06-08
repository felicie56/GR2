@php
    $imageItems = $post?->images ?? collect();
@endphp

@if ($imageItems->count() > 0)
    <section class="mt-10 rounded-[2rem] border border-white/10 bg-white/[0.04] overflow-hidden">
        <div class="p-5 md:p-6 border-b border-white/10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-black text-white">
                        Ảnh minh họa
                    </h2>

                    <p class="mt-2 text-sm text-slate-400">
                        Một số hình ảnh được tác giả thêm vào để minh họa cho nội dung bài viết.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                    {{ $imageItems->count() }} ảnh
                </div>
            </div>
        </div>

        <div class="p-5 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ($imageItems as $image)
                    <figure class="group overflow-hidden rounded-3xl border border-white/10 bg-slate-950/60">
                        <a href="{{ $image->image_path }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $image->image_path }}"
                                 alt="{{ $image->caption ?: $image->original_name ?: 'Ảnh minh họa bài viết' }}"
                                 class="h-72 w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                        </a>

                        @if ($image->caption || $image->original_name)
                            <figcaption class="p-4 text-sm text-slate-400 break-words [overflow-wrap:anywhere]">
                                {{ $image->caption ?: $image->original_name }}
                            </figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif