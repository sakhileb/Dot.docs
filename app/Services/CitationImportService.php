<?php

namespace App\Services;

class CitationImportService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function import(string $provider, string $jsonPayload): array
    {
        $decoded = json_decode($jsonPayload, true);

        if (! is_array($decoded)) {
            return [];
        }

        $items = $this->normalizeItems($decoded);

        $mapped = array_map(function (array $item) use ($provider): array {
            $creators = $item['creators'] ?? $item['authors'] ?? [];
            $authors = $this->authorsToString($creators);
            $year = $this->extractYear($item);

            return [
                'provider' => $provider,
                'external_id' => (string) ($item['key'] ?? $item['id'] ?? $item['uuid'] ?? ''),
                'title' => (string) ($item['title'] ?? $item['name'] ?? ''),
                'authors' => $authors,
                'publication_year' => $year,
                'source_url' => (string) ($item['url'] ?? $item['DOI'] ?? $item['doi'] ?? ''),
                'citation_text' => (string) ($item['citation'] ?? ''),
                'metadata' => $item,
            ];
        }, $items);

        return array_values(array_filter($mapped, fn (array $item) => trim($item['title']) !== ''));
    }

    /**
     * @param array<int|string, mixed> $decoded
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $decoded): array
    {
        if (array_is_list($decoded)) {
            return array_values(array_filter($decoded, 'is_array'));
        }

        foreach (['items', 'data', 'references'] as $key) {
            if (isset($decoded[$key]) && is_array($decoded[$key])) {
                return array_values(array_filter($decoded[$key], 'is_array'));
            }
        }

        return [];
    }

    /**
     * @param mixed $creators
     */
    private function authorsToString(mixed $creators): string
    {
        if (! is_array($creators)) {
            return '';
        }

        $names = [];

        foreach ($creators as $creator) {
            if (! is_array($creator)) {
                continue;
            }

            $first = trim((string) ($creator['firstName'] ?? $creator['given'] ?? ''));
            $last = trim((string) ($creator['lastName'] ?? $creator['family'] ?? $creator['name'] ?? ''));
            $full = trim($first.' '.$last);

            if ($full !== '') {
                $names[] = $full;
            }
        }

        return implode(', ', $names);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function extractYear(array $item): ?int
    {
        $raw = (string) ($item['year'] ?? $item['date'] ?? $item['issued']['date-parts'][0][0] ?? '');

        if (preg_match('/(19|20)\d{2}/', $raw, $matches) !== 1) {
            return null;
        }

        return (int) $matches[0];
    }
}
