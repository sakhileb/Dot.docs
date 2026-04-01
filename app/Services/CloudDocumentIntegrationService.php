<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CloudDocumentIntegrationService
{
    public function __construct(
        protected DocumentTransferService $documentTransferService,
    ) {
    }

    /**
     * @param array<int, Document> $documents
     */
    public function exportDocuments(array $documents, string $provider, string $format, User $user): int
    {
        $uploaded = 0;

        foreach ($documents as $document) {
            $result = $this->documentTransferService->exportDocument($document, $format, $user);

            $this->uploadBytes(
                $provider,
                $result['filename'],
                (string) ($result['bytes'] ?? ''),
                $result['mime'],
            );

            $uploaded++;
        }

        return $uploaded;
    }

    public function importDocument(string $provider, string $reference, ?string $title, User $user): Document
    {
        [$bytes, $extension, $detectedTitle] = $this->downloadFile($provider, $reference);
        $content = $this->documentTransferService->importFromContents($bytes, $extension);

        return Document::create([
            'team_id' => $user->currentTeam?->id,
            'user_id' => $user->id,
            'title' => trim((string) $title) !== ''
                ? trim((string) $title)
                : ($detectedTitle !== '' ? $detectedTitle : 'Imported Cloud Document'),
            'content' => $content,
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);
    }

    private function uploadBytes(string $provider, string $filename, string $bytes, string $mime): void
    {
        switch ($provider) {
            case 'google_drive':
                $this->uploadToGoogleDrive($filename, $bytes, $mime);

                return;
            case 'dropbox':
                $this->uploadToDropbox($filename, $bytes);

                return;
            case 'onedrive':
                $this->uploadToOneDrive($filename, $bytes);

                return;
            default:
                throw new \InvalidArgumentException('Unsupported cloud provider.');
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function downloadFile(string $provider, string $reference): array
    {
        return match ($provider) {
            'google_drive' => $this->downloadFromGoogleDrive($reference),
            'dropbox' => $this->downloadFromDropbox($reference),
            'onedrive' => $this->downloadFromOneDrive($reference),
            default => throw new \InvalidArgumentException('Unsupported cloud provider.'),
        };
    }

    private function uploadToGoogleDrive(string $filename, string $bytes, string $mime): void
    {
        $config = $this->providerConfig('google_drive');
        $metadata = [
            'name' => $filename,
        ];

        if ($config['folder_id'] !== null) {
            $metadata['parents'] = [$config['folder_id']];
        }

        $response = Http::withToken($config['access_token'])
            ->attach('metadata', json_encode($metadata, JSON_THROW_ON_ERROR), 'metadata.json', [
                'Content-Type' => 'application/json; charset=UTF-8',
            ])
            ->attach('file', $bytes, $filename, [
                'Content-Type' => $mime,
            ])
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink');

        if (! $response->successful()) {
            throw new \RuntimeException('Google Drive upload failed.');
        }
    }

    private function uploadToDropbox(string $filename, string $bytes): void
    {
        $config = $this->providerConfig('dropbox');
        $folderPath = trim((string) ($config['folder_path'] ?? ''), '/');
        $path = '/'.trim($folderPath.'/'.$filename, '/');

        $response = Http::withToken($config['access_token'])
            ->withHeaders([
                'Content-Type' => 'application/octet-stream',
                'Dropbox-API-Arg' => json_encode([
                    'path' => $path,
                    'mode' => 'add',
                    'autorename' => true,
                    'mute' => true,
                ], JSON_THROW_ON_ERROR),
            ])
            ->send('POST', 'https://content.dropboxapi.com/2/files/upload', [
                'body' => $bytes,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Dropbox upload failed.');
        }
    }

    private function uploadToOneDrive(string $filename, string $bytes): void
    {
        $config = $this->providerConfig('onedrive');
        $folder = trim((string) ($config['folder_path'] ?? ''), '/');
        $path = $folder !== '' ? $folder.'/'.$filename : $filename;

        $response = Http::withToken($config['access_token'])
            ->withHeaders([
                'Content-Type' => 'application/octet-stream',
            ])
            ->send('PUT', 'https://graph.microsoft.com/v1.0/me/drive/root:/'.str_replace('%2F', '/', rawurlencode($path)).':/content', [
                'body' => $bytes,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('OneDrive upload failed.');
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function downloadFromGoogleDrive(string $reference): array
    {
        $config = $this->providerConfig('google_drive');
        $fileId = $this->extractGoogleDriveFileId($reference);

        $metadataResponse = Http::withToken($config['access_token'])
            ->get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'fields' => 'id,name,mimeType',
            ]);

        if (! $metadataResponse->successful()) {
            throw new \RuntimeException('Unable to fetch Google Drive file metadata.');
        }

        $metadata = $metadataResponse->json();
        $mimeType = (string) ($metadata['mimeType'] ?? 'text/plain');
        $name = (string) ($metadata['name'] ?? 'google-drive-import.txt');

        if ($mimeType === 'application/vnd.google-apps.document') {
            $contentResponse = Http::withToken($config['access_token'])
                ->get("https://www.googleapis.com/drive/v3/files/{$fileId}/export", [
                    'mimeType' => 'text/plain',
                ]);

            if (! $contentResponse->successful()) {
                throw new \RuntimeException('Unable to export Google Docs document.');
            }

            return [(string) $contentResponse->body(), 'txt', pathinfo($name, PATHINFO_FILENAME) ?: 'Imported Google Doc'];
        }

        $contentResponse = Http::withToken($config['access_token'])
            ->get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'alt' => 'media',
            ]);

        if (! $contentResponse->successful()) {
            throw new \RuntimeException('Unable to download Google Drive file.');
        }

        return [(string) $contentResponse->body(), $this->resolveExtension($name), pathinfo($name, PATHINFO_FILENAME) ?: 'Imported Google Drive File'];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function downloadFromDropbox(string $reference): array
    {
        if (Str::startsWith($reference, ['http://', 'https://'])) {
            $directUrl = $this->normalizeDropboxSharedLink($reference);
            $response = Http::timeout(30)->get($directUrl);

            if (! $response->successful()) {
                throw new \RuntimeException('Unable to download Dropbox shared file.');
            }

            $filename = $this->extractFilenameFromContentDisposition($response->header('Content-Disposition'))
                ?? basename((string) parse_url($directUrl, PHP_URL_PATH))
                ?: 'dropbox-import.txt';

            return [(string) $response->body(), $this->resolveExtension($filename), pathinfo($filename, PATHINFO_FILENAME) ?: 'Imported Dropbox File'];
        }

        $config = $this->providerConfig('dropbox');
        $response = Http::withToken($config['access_token'])
            ->withHeaders([
                'Dropbox-API-Arg' => json_encode(['path' => $reference], JSON_THROW_ON_ERROR),
            ])
            ->send('POST', 'https://content.dropboxapi.com/2/files/download');

        if (! $response->successful()) {
            throw new \RuntimeException('Unable to download Dropbox file.');
        }

        $metadata = json_decode((string) $response->header('dropbox-api-result', '{}'), true);
        $filename = (string) ($metadata['name'] ?? basename($reference) ?: 'dropbox-import.txt');

        return [(string) $response->body(), $this->resolveExtension($filename), pathinfo($filename, PATHINFO_FILENAME) ?: 'Imported Dropbox File'];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function downloadFromOneDrive(string $reference): array
    {
        $config = $this->providerConfig('onedrive');

        if (Str::startsWith($reference, ['http://', 'https://'])) {
            $shareToken = $this->encodeOneDriveShareUrl($reference);
            $metadataResponse = Http::withToken($config['access_token'])
                ->get("https://graph.microsoft.com/v1.0/shares/{$shareToken}/driveItem", [
                    '$select' => 'name,@microsoft.graph.downloadUrl',
                ]);

            if (! $metadataResponse->successful()) {
                throw new \RuntimeException('Unable to fetch OneDrive shared file metadata.');
            }

            $metadata = $metadataResponse->json();
            $downloadUrl = (string) ($metadata['@microsoft.graph.downloadUrl'] ?? '');
            $filename = (string) ($metadata['name'] ?? 'onedrive-import.txt');
        } else {
            $metadataResponse = Http::withToken($config['access_token'])
                ->get("https://graph.microsoft.com/v1.0/me/drive/items/{$reference}", [
                    '$select' => 'name,@microsoft.graph.downloadUrl',
                ]);

            if (! $metadataResponse->successful()) {
                throw new \RuntimeException('Unable to fetch OneDrive file metadata.');
            }

            $metadata = $metadataResponse->json();
            $downloadUrl = (string) ($metadata['@microsoft.graph.downloadUrl'] ?? '');
            $filename = (string) ($metadata['name'] ?? 'onedrive-import.txt');
        }

        if ($downloadUrl === '') {
            throw new \RuntimeException('OneDrive file is missing a downloadable URL.');
        }

        $response = Http::timeout(30)->get($downloadUrl);

        if (! $response->successful()) {
            throw new \RuntimeException('Unable to download OneDrive file.');
        }

        return [(string) $response->body(), $this->resolveExtension($filename), pathinfo($filename, PATHINFO_FILENAME) ?: 'Imported OneDrive File'];
    }

    /**
     * @return array<string, mixed>
     */
    private function providerConfig(string $provider): array
    {
        $config = match ($provider) {
            'google_drive' => config('services.google_drive', []),
            'dropbox' => config('services.dropbox', []),
            'onedrive' => config('services.onedrive', []),
            default => throw new \InvalidArgumentException('Unsupported cloud provider.'),
        };

        if (! filled($config['access_token'] ?? null)) {
            throw new \InvalidArgumentException(match ($provider) {
                'google_drive' => 'Google Drive integration is not configured.',
                'dropbox' => 'Dropbox integration is not configured.',
                default => 'OneDrive integration is not configured.',
            });
        }

        return $config;
    }

    private function extractGoogleDriveFileId(string $reference): string
    {
        if (preg_match('#/(?:document|file)/d/([a-zA-Z0-9_-]+)#', $reference, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $reference) === 1) {
            return $reference;
        }

        throw new \InvalidArgumentException('Invalid Google Drive file reference.');
    }

    private function normalizeDropboxSharedLink(string $url): string
    {
        $directUrl = preg_replace('/\?dl=0$/', '?dl=1', $url) ?? $url;

        if (! str_contains($directUrl, 'dl=')) {
            $separator = str_contains($directUrl, '?') ? '&' : '?';
            $directUrl .= $separator.'dl=1';
        }

        return $directUrl;
    }

    private function encodeOneDriveShareUrl(string $url): string
    {
        return 'u!'.rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
    }

    private function resolveExtension(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'html', 'htm', 'md', 'markdown', 'txt', 'docx' => $extension,
            default => 'txt',
        };
    }

    private function extractFilenameFromContentDisposition(?string $contentDisposition): ?string
    {
        if (! is_string($contentDisposition) || $contentDisposition === '') {
            return null;
        }

        if (preg_match('/filename="?([^";]+)"?/i', $contentDisposition, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }
}