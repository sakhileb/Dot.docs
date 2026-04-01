<?php

namespace App\Services;

class MailMergeService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseRecipients(string $jsonPayload): array
    {
        $decoded = json_decode($jsonPayload, true);

        if (! is_array($decoded)) {
            return [];
        }

        if (! array_is_list($decoded)) {
            if (isset($decoded['recipients']) && is_array($decoded['recipients'])) {
                $decoded = $decoded['recipients'];
            } else {
                return [];
            }
        }

        return array_values(array_filter($decoded, 'is_array'));
    }

    /**
     * @param array<string, mixed> $recipient
     */
    public function mergeTemplate(string $template, array $recipient): string
    {
        return preg_replace_callback('/{{\s*([a-zA-Z0-9_\.]+)\s*}}/', function (array $matches) use ($recipient): string {
            $key = $matches[1];
            $value = data_get($recipient, $key);

            if (is_scalar($value) || $value === null) {
                return (string) ($value ?? '');
            }

            return '';
        }, $template) ?? $template;
    }

    /**
     * @param array<int, array<string, mixed>> $recipients
     * @return array<int, array<string, mixed>>
     */
    public function buildMergedDocuments(string $template, array $recipients): array
    {
        $documents = [];

        foreach ($recipients as $index => $recipient) {
            $documents[] = [
                'index' => $index + 1,
                'recipient' => $recipient,
                'content' => $this->mergeTemplate($template, $recipient),
            ];
        }

        return $documents;
    }
}
