<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class EditorLazyLoadingService
{
    protected string $chunkSize = '1000'; // words per chunk

    public function getContentChunks(string $content, int $chunkWords = 1000): array
    {
        $words = explode(' ', $content);
        $chunks = [];

        for ($i = 0; $i < count($words); $i += $chunkWords) {
            $chunk = array_slice($words, $i, $chunkWords);
            $chunks[] = [
                'index' => count($chunks),
                'content' => implode(' ', $chunk),
                'wordCount' => count($chunk),
                'startWord' => $i,
                'endWord' => $i + count($chunk),
            ];
        }

        return $chunks;
    }

    public function getCachedChunk(int $documentId, int $chunkIndex): ?array
    {
        return Cache::remember(
            "document.{$documentId}.chunk.{$chunkIndex}",
            now()->addHours(6),
            function () use ($documentId, $chunkIndex) {
                $document = \App\Models\Document::find($documentId);
                if (!$document) {
                    return null;
                }

                $chunks = $this->getContentChunks($document->content);
                return $chunks[$chunkIndex] ?? null;
            }
        );
    }

    /**
     * Get initial visible chunks for viewport
     */
    public function getInitialChunks(string $content, int $initialWords = 3000): array
    {
        $words = explode(' ', $content);
        $initialContent = implode(' ', array_slice($words, 0, $initialWords));

        return $this->getContentChunks($initialContent);
    }

    /**
     * Clear cache for document
     */
    public function clearCache(int $documentId): void
    {
        Cache::tags(["document.{$documentId}"])->flush();
    }

    /**
     * Detect if content should be paginated
     */
    public function shouldPaginate(string $content): bool
    {
        $wordCount = str_word_count($content);
        return $wordCount > 5000;
    }

    /**
     * Get estimated read time
     */
    public function getEstimatedReadTime(string $content): int
    {
        $wordCount = str_word_count($content);
        // Average reading speed: 200 words per minute
        return max(1, ceil($wordCount / 200));
    }
}
