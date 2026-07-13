<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class NewsContentPresenter
{
    /**
     * Chuyển nội dung News thành các block có thể render riêng lẻ.
     *
     * - Nội dung CKEditor: giữ nguyên từng block HTML cấp cao nhất.
     * - Nội dung RSS/text cũ: tách theo đoạn trống và bọc bằng <p>.
     *
     * @return array<int, string>
     */
    public function toBlocks(?string $content): array
    {
        $content = trim((string) $content);

        if ($content === '') {
            return [];
        }

        if (! $this->containsRichTextMarkup($content)) {
            return $this->plainTextToBlocks($content);
        }

        $blocks = $this->htmlToBlocks($content);

        if ($blocks !== []) {
            return $blocks;
        }

        return $this->plainTextToBlocks(
            html_entity_decode(
                strip_tags($content),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );
    }

    public function containsRichTextMarkup(string $content): bool
    {
        return (bool) preg_match(
            '/<(p|h2|h3|h4|blockquote|ul|ol|figure|img|strong|em|a)\b/i',
            $content
        );
    }

    /**
     * @return array<int, string>
     */
    private function plainTextToBlocks(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $paragraphs = preg_split('/\n\s*\n+/u', $content) ?: [];

        if (count($paragraphs) <= 1) {
            $paragraphs = $this->splitLongPlainText($content);
        }

        return collect($paragraphs)
            ->map(function (string $paragraph): string {
                $paragraph = trim(
                    preg_replace('/[ \t]+/u', ' ', $paragraph) ?? $paragraph
                );

                return $paragraph === ''
                    ? ''
                    : '<p>' . nl2br(e($paragraph), false) . '</p>';
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Nếu RSS chỉ trả về một đoạn text dài, chia thành các cụm 2 câu để
     * hệ thống vẫn có vị trí hợp lý để chèn “Tin liên quan”.
     *
     * @return array<int, string>
     */
    private function splitLongPlainText(string $content): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/u', trim($content)) ?: [];

        if (count($sentences) <= 2) {
            return [trim($content)];
        }

        $blocks = [];
        $current = [];

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            if ($sentence === '') {
                continue;
            }

            $current[] = $sentence;

            if (count($current) === 2) {
                $blocks[] = implode(' ', $current);
                $current = [];
            }
        }

        if ($current !== []) {
            $blocks[] = implode(' ', $current);
        }

        return $blocks ?: [trim($content)];
    }

    /**
     * @return array<int, string>
     */
    private function htmlToBlocks(string $html): array
    {
        if (! class_exists(DOMDocument::class)) {
            return [];
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapperId = 'news-content-root-' . bin2hex(random_bytes(4));

        $document = '<!DOCTYPE html><html><body>'
            . '<div id="' . $wrapperId . '">' . $html . '</div>'
            . '</body></html>';

        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $dom->loadHTML(
                '<?xml encoding="UTF-8">' . $document,
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }

        if (! $loaded) {
            return [];
        }

        $root = $dom->getElementById($wrapperId);

        if (! $root instanceof DOMElement) {
            return [];
        }

        $blocks = [];

        foreach ($root->childNodes as $node) {
            $block = $this->serializeNode($dom, $node);

            if ($block !== '') {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    private function serializeNode(DOMDocument $dom, DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = trim((string) $node->textContent);

            return $text === ''
                ? ''
                : '<p>' . e($text) . '</p>';
        }

        $html = trim((string) $dom->saveHTML($node));

        return preg_replace('/^<\?xml[^>]+>/i', '', $html) ?? $html;
    }
}