<?php

namespace App\Observers;

use App\Jobs\DeleteKnowledgeDocumentJob;
use App\Jobs\SyncKnowledgeDocumentJob;
use App\Models\AiKnowledgeDocument;
use App\Models\News;

class NewsObserver
{
    /**
     * News do admin tạo hoặc crawler tạo
     * đều được đồng bộ.
     */
    public function saved(
        News $news
    ): void {
        SyncKnowledgeDocumentJob::dispatch(
            AiKnowledgeDocument::TYPE_NEWS,
            (int) $news->id
        );
    }

    /**
     * Khi News bị xóa,
     * tài liệu cũng bị gỡ khỏi Vector Store.
     */
    public function deleted(
        News $news
    ): void {
        DeleteKnowledgeDocumentJob::dispatch(
            AiKnowledgeDocument::TYPE_NEWS,
            (int) $news->id
        );
    }
}