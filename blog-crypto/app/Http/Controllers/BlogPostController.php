<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogPostImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index(Request $request)
{
    $search = $request->query('search');
    $categoryId = $request->query('category');

    $categories = $this->activeCategories();

    $query = BlogPost::with(['author', 'category', 'images', 'comments', 'reactions'])
        ->when(Schema::hasColumn('blog_posts', 'status'), function ($query) {
            $query->where(function ($statusQuery) {
                $statusQuery->where('status', 'approved')
                    ->orWhere('status', 'APPROVED');
            });
        })
        ->when($search, function ($query) use ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        })
        ->when($categoryId && Schema::hasColumn('blog_posts', 'category_id'), function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        });

    if (Schema::hasColumn('blog_posts', 'published_at')) {
        $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    } else {
        $query->latest('created_at');
    }

    $posts = $query
        ->paginate(9)
        ->withQueryString();

    /*
     * Lấy bài đầu tiên trong danh sách hiện tại làm featured.
     * $posts vẫn giữ toàn bộ paginator để view biết thật sự có bài.
     */
    $featuredPost = $posts->getCollection()->first();

    /*
     * Các bài còn lại để render grid, tránh lặp lại featured.
     */
    $remainingPosts = $posts->getCollection()
        ->skip($featuredPost ? 1 : 0)
        ->values();

    return view('blog.index', compact(
        'posts',
        'remainingPosts',
        'featuredPost',
        'categories',
        'search',
        'categoryId'
    ));
}

    public function show(string $slug)
    {
        $post = BlogPost::with([
                'author',
                'category',
                'images',
                'comments.user',
                'reactions',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', compact('post'));
    }

    public function create()
    {
        $categories = $this->activeCategories();

        return view('blog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateBlogPost($request);

        $data = [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'content' => $validated['content'],
        ];

        if (Schema::hasColumn('blog_posts', 'category_id')) {
            $data['category_id'] = $validated['category_id'] ?? null;
        }

        if (Schema::hasColumn('blog_posts', 'user_id')) {
            $data['user_id'] = auth()->id();
        }

        if (Schema::hasColumn('blog_posts', 'author_id')) {
            $data['author_id'] = auth()->id();
        }

        if (Schema::hasColumn('blog_posts', 'status')) {
            $data['status'] = 'pending';
        }

        if (Schema::hasColumn('blog_posts', 'review_seen_at')) {
            $data['review_seen_at'] = null;
        }

        if (Schema::hasColumn('blog_posts', 'thumbnail')) {
            $data['thumbnail'] = $validated['thumbnail'] ?? null;

            if ($request->hasFile('thumbnail_file')) {
                $data['thumbnail'] = $this->storeUploadedFile($request->file('thumbnail_file'), 'blog-thumbnails');
            }
        }

        $post = BlogPost::create($data);

        $this->storeUploadedImages($request, $post);

        return redirect()
            ->route('blog.my')
            ->with('success', 'Bài viết đã được gửi và đang chờ admin duyệt.');
    }

    public function myPosts()
    {
        $posts = BlogPost::with(['category', 'images', 'comments', 'reactions'])
            ->where(function ($query) {
                if (Schema::hasColumn('blog_posts', 'user_id')) {
                    $query->where('user_id', auth()->id());
                }

                if (Schema::hasColumn('blog_posts', 'author_id')) {
                    $query->orWhere('author_id', auth()->id());
                }
            })
            ->latest()
            ->paginate(10);

        return view('blog.my', compact('posts'));
    }

    public function edit(BlogPost $post)
    {
        $this->authorizePostOwner($post);

        $post->load(['images', 'category']);

        $categories = $this->activeCategories();

        return view('blog.edit', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $this->authorizePostOwner($post);

        $validated = $this->validateBlogPost($request, isUpdate: true);

        $data = [
            'title' => $validated['title'],
            'content' => $validated['content'],
        ];

        if ($post->title !== $validated['title']) {
            $data['slug'] = $this->uniqueSlug($validated['title'], $post->id);
        }

        if (Schema::hasColumn('blog_posts', 'category_id')) {
            $data['category_id'] = $validated['category_id'] ?? null;
        }

        if (Schema::hasColumn('blog_posts', 'thumbnail')) {
            if ($request->boolean('remove_thumbnail')) {
                $this->deleteStorageUrl($post->thumbnail);
                $data['thumbnail'] = null;
            } else {
                $data['thumbnail'] = $validated['thumbnail'] ?? $post->thumbnail;
            }

            if ($request->hasFile('thumbnail_file')) {
                $this->deleteStorageUrl($post->thumbnail);
                $data['thumbnail'] = $this->storeUploadedFile($request->file('thumbnail_file'), 'blog-thumbnails');
            }
        }

        /*
         * Khi author sửa bài, đưa bài về trạng thái pending để admin duyệt lại.
         * Nếu tài khoản admin sửa trực tiếp thì không ép pending.
         */
        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('ADMIN');

        if (! $isAdmin && Schema::hasColumn('blog_posts', 'status')) {
            $data['status'] = 'pending';

            if (Schema::hasColumn('blog_posts', 'reviewed_at')) {
                $data['reviewed_at'] = null;
            }

            if (Schema::hasColumn('blog_posts', 'reviewed_by')) {
                $data['reviewed_by'] = null;
            }

            if (Schema::hasColumn('blog_posts', 'rejection_reason')) {
                $data['rejection_reason'] = null;
            }

            if (Schema::hasColumn('blog_posts', 'review_note')) {
                $data['review_note'] = null;
            }

            if (Schema::hasColumn('blog_posts', 'review_seen_at')) {
                $data['review_seen_at'] = null;
            }
        }

        $post->update($data);

        $this->deleteSelectedImages($request, $post);
        $this->storeUploadedImages($request, $post);

        return redirect()
            ->route('blog.my')
            ->with('success', 'Bài viết đã được cập nhật.');
    }

    public function destroy(BlogPost $post)
    {
        $this->authorizePostOwner($post);

        $post->load('images');

        $this->deleteStorageUrl($post->thumbnail);

        foreach ($post->images as $image) {
            $this->deleteStorageUrl($image->image_path);
        }

        $post->delete();

        return redirect()
            ->route('blog.my')
            ->with('success', 'Đã xóa bài viết.');
    }

    public function deleteImage(BlogPostImage $image)
    {
        $post = $image->blogPost;

        $this->authorizePostOwner($post);

        $this->deleteStorageUrl($image->image_path);

        $image->delete();

        return back()->with('success', 'Đã xóa ảnh minh họa.');
    }

    public function pending()
    {
        $posts = BlogPost::with(['author', 'category', 'images', 'comments', 'reactions'])
            ->when(Schema::hasColumn('blog_posts', 'status'), function ($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->paginate(10);

        return view('admin.blogs.pending', [
            'posts' => $posts,
            'pendingPosts' => $posts,
        ]);
    }

    public function approve(BlogPost $post)
{
    $data = [];

    if (Schema::hasColumn('blog_posts', 'status')) {
        $data['status'] = 'approved';
    }

    if (Schema::hasColumn('blog_posts', 'reviewed_at')) {
        $data['reviewed_at'] = now();
    }

    if (Schema::hasColumn('blog_posts', 'reviewed_by')) {
        $data['reviewed_by'] = auth()->id();
    }

    if (Schema::hasColumn('blog_posts', 'published_at')) {
        $data['published_at'] = now();
    }

    if (Schema::hasColumn('blog_posts', 'rejection_reason')) {
        $data['rejection_reason'] = null;
    }

    if (Schema::hasColumn('blog_posts', 'review_note')) {
        $data['review_note'] = null;
    }

    if (Schema::hasColumn('blog_posts', 'review_seen_at')) {
        $data['review_seen_at'] = null;
    }

    $post->update($data);

    return redirect()
        ->route('admin.blog.pending')
        ->with('success', 'Đã duyệt bài viết. Bài viết hiện đã được công khai trên trang Blog.');
}

    public function reject(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = [];

        if (Schema::hasColumn('blog_posts', 'status')) {
            $data['status'] = 'rejected';
        }

        if (Schema::hasColumn('blog_posts', 'reviewed_at')) {
            $data['reviewed_at'] = now();
        }

        if (Schema::hasColumn('blog_posts', 'reviewed_by')) {
            $data['reviewed_by'] = auth()->id();
        }

        if (Schema::hasColumn('blog_posts', 'rejection_reason')) {
            $data['rejection_reason'] = $validated['rejection_reason'] ?? 'Bài viết chưa đáp ứng yêu cầu kiểm duyệt.';
        }

        if (Schema::hasColumn('blog_posts', 'review_seen_at')) {
            $data['review_seen_at'] = null;
        }

        $post->update($data);

        return back()->with('success', 'Đã từ chối bài viết.');
    }

    public function markReviewSeen(BlogPost $post)
    {
        $this->authorizePostOwner($post);

        if (Schema::hasColumn('blog_posts', 'review_seen_at')) {
            $post->update([
                'review_seen_at' => now(),
            ]);
        }

        return back();
    }

    private function validateBlogPost(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'category_id' => ['nullable', 'exists:categories,id'],

            /*
             * thumbnail: URL nhập tay cũ.
             * thumbnail_file: upload ảnh đại diện từ máy.
             */
            'thumbnail' => ['nullable', 'string', 'max:1000'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_thumbnail' => ['nullable', 'boolean'],

            /*
             * images[]: ảnh minh họa phụ.
             */
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            /*
             * edit bài: chọn ảnh cũ cần xóa.
             */
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer', 'exists:blog_post_images,id'],
        ]);
    }

    private function activeCategories()
    {
        $query = Category::query();

        if (Schema::hasColumn('categories', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    private function storeUploadedImages(Request $request, BlogPost $post): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $currentMaxSort = (int) $post->images()->max('sort_order');

        foreach ($request->file('images') as $index => $imageFile) {
            if (! $imageFile || ! $imageFile->isValid()) {
                continue;
            }

            $imagePath = $this->storeUploadedFile($imageFile, 'blog-images');

            BlogPostImage::create([
                'blog_post_id' => $post->id,
                'image_path' => $imagePath,
                'original_name' => $imageFile->getClientOriginalName(),
                'sort_order' => $currentMaxSort + $index + 1,
            ]);
        }
    }

    private function deleteSelectedImages(Request $request, BlogPost $post): void
    {
        $imageIds = collect($request->input('delete_image_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($imageIds->isEmpty()) {
            return;
        }

        $images = $post->images()
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            $this->deleteStorageUrl($image->image_path);
            $image->delete();
        }
    }

    private function storeUploadedFile($file, string $folder): string
    {
        $path = $file->store($folder, 'public');

        return Storage::url($path);
    }

    private function deleteStorageUrl(?string $url): void
    {
        if (! $url) {
            return;
        }

        /*
         * Không xóa ảnh ngoài như https://...
         */
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return;
        }

        $path = $url;

        if (Str::startsWith($path, '/storage/')) {
            $path = Str::after($path, '/storage/');
        } elseif (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        if (! $baseSlug) {
            $baseSlug = 'blog-' . Str::random(8);
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            BlogPost::where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function authorizePostOwner(BlogPost $post): void
    {
        $user = auth()->user();

        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('ADMIN');

        $ownerId = $post->user_id ?? $post->author_id ?? null;

        abort_unless($isAdmin || (int) $ownerId === (int) auth()->id(), 403);
    }
}