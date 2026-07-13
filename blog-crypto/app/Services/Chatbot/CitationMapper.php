<?php

namespace App\Services\Chatbot;

use App\Models\AiKnowledgeDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CitationMapper
{
    /**
     * @param  array<int, array<string, mixed>>  $annotations
     * @param  array<int, array<string, mixed>>  $searchResults
     * @param  array<int, array<string, mixed>>  $sourceHints
     * @return array<int, array<string, mixed>>
     */
    public function map(
        array $annotations,
        array $searchResults = [],
        array $sourceHints = []
    ): array {
        $searchByFileId = collect($searchResults)
            ->filter(fn ($item) => is_array($item))
            ->keyBy(fn ($item) => (string) ($item['file_id'] ?? $item['id'] ?? ''));

        $fileIds = collect($annotations)
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => (string) ($item['file_id'] ?? ''))
            ->filter()
            ->unique()
            ->values();

        if ($fileIds->isEmpty()) {
            $fileIds = $searchByFileId->keys()
                ->filter()
                ->take(max(1, (int) config(
                    'chatbot.retrieval.max_results',
                    4
                )))
                ->values();
        }

        /** @var Collection<string, AiKnowledgeDocument> $documents */
        $documents = AiKnowledgeDocument::query()
            ->where(function ($query) use ($fileIds) {
                $query->whereIn('openai_file_id', $fileIds)
                    ->orWhereIn('vector_store_file_id', $fileIds);
            })
            ->where('status', AiKnowledgeDocument::STATUS_INDEXED)
            ->get()
            ->flatMap(function (AiKnowledgeDocument $document) {
                $pairs = [];

                if ($document->openai_file_id) {
                    $pairs[$document->openai_file_id] = $document;
                }

                if ($document->vector_store_file_id) {
                    $pairs[$document->vector_store_file_id] = $document;
                }

                return $pairs;
            });

        $sources = [];

        foreach ($fileIds as $fileId) {
            $document = $documents->get($fileId);

            if (! $document) {
                continue;
            }

            $result = $searchByFileId->get($fileId, []);
            $excerpt = $this->extractResultText(is_array($result) ? $result : []);
            $metadata = is_array($document->metadata)
                ? $document->metadata
                : [];

            $sources[] = [
                'type' => $document->source_type,
                'id' => $document->source_id,
                'title' => $document->title,
                'url' => $document->public_url,
                'excerpt' => $excerpt,
                'published_at' => $metadata['published_at'] ?? null,
                'source_name' => $metadata['source'] ?? null,
            ];
        }

        foreach ($sourceHints as $hint) {
            if (! is_array($hint) || empty($hint['url'])) {
                continue;
            }

            $sources[] = [
                'type' => $hint['type'] ?? 'link',
                'id' => $hint['id'] ?? null,
                'title' => $hint['title'] ?? 'Nội dung liên quan',
                'url' => $hint['url'],
                'excerpt' => $hint['excerpt'] ?? null,
                'published_at' => $hint['published_at'] ?? null,
                'source_name' => $hint['source_name'] ?? null,
            ];
        }

        return collect($sources)
            ->unique(fn ($source) => ($source['type'] ?? '') . '|' . ($source['url'] ?? ''))
            ->values()
            ->take(6)
            ->all();
    }

    /** @param array<string, mixed> $result */
    private function extractResultText(array $result): ?string
    {
        $text = $result['text'] ?? data_get($result, 'content.0.text');

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

        return Str::limit($text, 220, '…');
    }
}