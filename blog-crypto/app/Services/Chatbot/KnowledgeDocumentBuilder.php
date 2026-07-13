<?php

namespace App\Services\Chatbot;

use App\Models\AiKnowledgeDocument;
use App\Models\BlogPost;
use App\Models\News;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class KnowledgeDocumentBuilder
{
    /**
     * Chuyển Blog hoặc News thành dữ liệu
     * có thể upload lên OpenAI.
     *
     * @return array{
     *     source_type: string,
     *     source_id: int,
     *     title: string,
     *     slug: ?string,
     *     public_url: string,
     *     filename: string,
     *     content: string,
     *     content_hash: string,
     *     metadata: array<string, mixed>,
     *     vector_attributes: array<string, string|int|float|bool>
     * }
     */
    public function build(
        Model $source
    ): array {
        if ($source instanceof BlogPost) {
            return $this->buildBlog(
                $source
            );
        }

        if ($source instanceof News) {
            return $this->buildNews(
                $source
            );
        }

        throw new InvalidArgumentException(
            'Chỉ hỗ trợ lập chỉ mục '
            . 'BlogPost hoặc News.'
        );
    }

    /**
     * Chỉ Blog approved mới được index.
     * News luôn có thể được index.
     */
    public function isEligible(
        Model $source
    ): bool {
        if ($source instanceof BlogPost) {
            return strtolower(
                (string) $source->status
            ) === 'approved';
        }

        return $source instanceof News;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBlog(
        BlogPost $post
    ): array {
        $post->loadMissing([
            'category',
            'author',
        ]);

        $title = trim(
            (string) $post->title
        );

        $slug = $post->slug
            ? (string) $post->slug
            : null;

        $publicUrl = route(
            'blog.show',
            $slug ?: $post->id
        );

        $contentText = $this->htmlToText(
            (string) $post->content
        );

        $publishedAt = optional(
            $post->published_at
            ?? $post->created_at
        )->toIso8601String();

        $markdown = $this->markdown(
            [
                'Loại nội dung' => 'Blog',
                'Tiêu đề' => $title,
                'URL công khai' => $publicUrl,
                'Chuyên mục' =>
                    $post->category?->name,
                'Tác giả' =>
                    $post->author?->name,
                'Ngày xuất bản' =>
                    $publishedAt,
            ],
            $contentText
        );

        return $this->payload(
            sourceType:
                AiKnowledgeDocument::TYPE_BLOG,

            sourceId:
                (int) $post->id,

            title:
                $title,

            slug:
                $slug,

            publicUrl:
                $publicUrl,

            filename:
                'blog-'
                . $post->id
                . '-'
                . Str::slug($title)
                . '.md',

            content:
                $markdown,

            metadata: [
                'category' =>
                    $post->category?->name,

                'author' =>
                    $post->author?->name,

                'published_at' =>
                    $publishedAt,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNews(
        News $news
    ): array {
        $news->loadMissing(
            'category'
        );

        $title = trim(
            (string) $news->title
        );

        $slug = $news->slug
            ? (string) $news->slug
            : null;

        $publicUrl = route(
            'news.show',
            $slug ?: $news->id
        );

        $summary = $this->htmlToText(
            (string) (
                $news->summary ?? ''
            )
        );

        $contentText = $this->htmlToText(
            (string) $news->content
        );

        $publishedAt = optional(
            $news->published_at
            ?? $news->created_at
        )->toIso8601String();

        $body = trim(
            (
                $summary !== ''
                    ? "Tóm tắt:\n{$summary}\n\n"
                    : ''
            )
            . "Nội dung:\n{$contentText}"
        );

        $markdown = $this->markdown(
            [
                'Loại nội dung' => 'Tin tức',
                'Tiêu đề' => $title,
                'URL công khai' => $publicUrl,
                'Chuyên mục' =>
                    $news->category?->name,
                'Nguồn' =>
                    $news->source,
                'Đường dẫn nguồn gốc' =>
                    $news->source_url,
                'Ngày xuất bản' =>
                    $publishedAt,
            ],
            $body
        );

        return $this->payload(
            sourceType:
                AiKnowledgeDocument::TYPE_NEWS,

            sourceId:
                (int) $news->id,

            title:
                $title,

            slug:
                $slug,

            publicUrl:
                $publicUrl,

            filename:
                'news-'
                . $news->id
                . '-'
                . Str::slug($title)
                . '.md',

            content:
                $markdown,

            metadata: [
                'category' =>
                    $news->category?->name,

                'source' =>
                    $news->source,

                'source_url' =>
                    $news->source_url,

                'published_at' =>
                    $publishedAt,

                'is_auto' =>
                    (bool) $news->is_auto,
            ]
        );
    }

    /**
     * Chuẩn hóa payload dùng để lưu DB
     * và upload OpenAI.
     *
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private function payload(
        string $sourceType,
        int $sourceId,
        string $title,
        ?string $slug,
        string $publicUrl,
        string $filename,
        string $content,
        array $metadata
    ): array {
        $filename = trim(
            $filename,
            '-'
        );

        if (
            ! str_ends_with(
                $filename,
                '.md'
            )
        ) {
            $filename .= '.md';
        }

        return [
            'source_type' =>
                $sourceType,

            'source_id' =>
                $sourceId,

            'title' =>
                $title,

            'slug' =>
                $slug,

            'public_url' =>
                $publicUrl,

            'filename' =>
                $filename,

            'content' =>
                $content,

            'content_hash' =>
                hash(
                    'sha256',
                    $content
                ),

            'metadata' =>
                $metadata,

            'vector_attributes' => [
                'source_type' =>
                    $sourceType,

                'source_id' =>
                    $sourceId,

                'title' =>
                    Str::limit(
                        $title,
                        240,
                        ''
                    ),

                'public_url' =>
                    Str::limit(
                        $publicUrl,
                        500,
                        ''
                    ),
            ],
        ];
    }

    /**
     * Tạo nội dung Markdown có metadata.
     *
     * @param array<string, mixed> $fields
     */
    private function markdown(
        array $fields,
        string $body
    ): string {
        $lines = [];

        foreach (
            $fields as $label => $value
        ) {
            if (
                $value === null
                || trim((string) $value) === ''
            ) {
                continue;
            }

            $lines[] =
                "- {$label}: "
                . trim((string) $value);
        }

        return trim(
            '# '
            . (
                $fields['Tiêu đề']
                ?? 'Nội dung'
            )
            . "\n\n"
            . implode("\n", $lines)
            . "\n\n---\n\n"
            . trim($body)
        );
    }

    /**
     * Loại HTML và chuyển về text sạch
     * để đưa vào Vector Store.
     */
    private function htmlToText(
        string $html
    ): string {
        $html = preg_replace(
            '/<(script|style|noscript)[^>]*>.*?<\/\1>/is',
            ' ',
            $html
        ) ?? $html;

        $html = preg_replace(
            '/<\/?(p|div|section|article|h1|h2|h3|h4|h5|h6|li|blockquote|figcaption|br)[^>]*>/i',
            "\n",
            $html
        ) ?? $html;

        $text = html_entity_decode(
            strip_tags($html),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace(
            '/[\t ]+/u',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\n{3,}/u',
            "\n\n",
            $text
        ) ?? $text;

        return trim(
            $text
        );
    }
}