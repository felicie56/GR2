<?php

namespace App\Observers;

use App\Jobs\DeleteKnowledgeDocumentJob;
use App\Jobs\SyncKnowledgeDocumentJob;
use App\Models\AiKnowledgeDocument;
use App\Models\BlogPost;

class BlogPostObserver
{
    /**
     * Chạy khi Blog được tạo hoặc cập nhật.
     */
    public function saved(
        BlogPost $post
    ): void {
        /*
         * Chỉ bài đã được admin duyệt
         * mới được đưa vào chatbot.
         */
        if (
            strtolower(
                (string) $post->status
            ) === 'approved'
        ) {
            SyncKnowledgeDocumentJob::dispatch(
                AiKnowledgeDocument::TYPE_BLOG,
                (int) $post->id
            );

            return;
        }

        /*
         * Nếu author sửa bài và trạng thái
         * trở lại pending/rejected,
         * phải gỡ bản cũ khỏi Vector Store.
         */
        DeleteKnowledgeDocumentJob::dispatch(
            AiKnowledgeDocument::TYPE_BLOG,
            (int) $post->id
        );
    }

    /**
     * Khi Blog bị xóa,
     * gỡ tài liệu khỏi OpenAI.
     */
    public function deleted(
        BlogPost $post
    ): void {
        DeleteKnowledgeDocumentJob::dispatch(
            AiKnowledgeDocument::TYPE_BLOG,
            (int) $post->id
        );
    }
}