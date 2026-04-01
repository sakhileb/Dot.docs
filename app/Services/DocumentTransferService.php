<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentExportJob;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use League\HTMLToMarkdown\HtmlConverter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html as PhpWordHtml;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DocumentTransferService
{
    public function exportDocument(Document $document, string $format, User $user): array
    {
        $format = strtolower($format);
        $content = (string) ($document->content ?? '');

        $job = DocumentExportJob::create([
            'document_id' => $document->id,
            'team_id' => $document->team_id,
            'user_id' => $user->id,
            'format' => $format,
            'status' => 'processing',
            'requested_at' => now(),
        ]);

        try {
            [$bytes, $extension, $mime] = match ($format) {
                'pdf' => [$this->toPdf($document->title, $content), 'pdf', 'application/pdf'],
                'docx' => [$this->toDocx($document->title, $content), 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'html' => [$this->toHtml($document->title, $content), 'html', 'text/html'],
                'md' => [$this->toMarkdown($content), 'md', 'text/markdown'],
                'txt' => [$this->toText($content), 'txt', 'text/plain'],
                default => throw new \InvalidArgumentException('Unsupported export format.'),
            };

            $filename = $this->filename($document->title, $extension);
            $path = 'exports/' . now()->format('Y/m/d') . '/' . uniqid('doc_', true) . '_' . $filename;
            Storage::disk('local')->put($path, $bytes);

            $job->update([
                'status' => 'completed',
                'file_path' => $path,
                'completed_at' => now(),
            ]);

            return [
                'path' => storage_path('app/' . $path),
                'filename' => $filename,
                'mime' => $mime,
                'bytes' => $bytes,
            ];
        } catch (\Throwable $e) {
            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * @param array<int, Document> $documents
     */
    public function exportBatch(array $documents, string $format, User $user): array
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'dotdocs_batch_');
        $zip = new ZipArchive();

        if (! $zipFile || $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create batch export archive.');
        }

        foreach ($documents as $document) {
            $result = $this->exportDocument($document, $format, $user);
            $zip->addFile($result['path'], $result['filename']);
        }

        $zip->close();

        return [
            'path' => $zipFile,
            'filename' => 'dotdocs-batch-' . now()->format('Ymd-His') . '.zip',
            'mime' => 'application/zip',
        ];
    }

    public function importFromFile(string $filePath, string $extension): string
    {
        $ext = strtolower($extension);

        return match ($ext) {
            'html', 'htm' => (string) file_get_contents($filePath),
            'md', 'markdown' => $this->plainTextToHtml((string) file_get_contents($filePath)),
            'txt' => $this->plainTextToHtml((string) file_get_contents($filePath)),
            'docx' => $this->docxToHtml($filePath),
            default => throw new \InvalidArgumentException('Unsupported import format.'),
        };
    }

    public function importFromContents(string $contents, string $extension): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dotdocs_import_');

        if (! $tmp) {
            throw new \RuntimeException('Unable to allocate temporary file for import.');
        }

        file_put_contents($tmp, $contents);

        try {
            return $this->importFromFile($tmp, $extension);
        } finally {
            @unlink($tmp);
        }
    }

    private function toPdf(string $title, string $content): string
    {
        $html = $this->toHtml($title, $content);

        return Pdf::loadHTML($html)->output();
    }

    private function toDocx(string $title, string $content): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addTitle($title, 1);
        PhpWordHtml::addHtml($section, $content !== '' ? $content : '<p></p>');

        $tmp = tempnam(sys_get_temp_dir(), 'dotdocs_docx_');
        if (! $tmp) {
            throw new \RuntimeException('Unable to allocate temporary file for DOCX export.');
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmp);
        $bytes = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $bytes;
    }

    private function toHtml(string $title, string $content): string
    {
        $safeTitle = e($title);

        return "<!doctype html><html><head><meta charset=\"utf-8\"><title>{$safeTitle}</title></head><body>{$content}</body></html>";
    }

    private function toMarkdown(string $content): string
    {
        $converter = new HtmlConverter();

        return $converter->convert($content);
    }

    private function toText(string $content): string
    {
        return trim(html_entity_decode(strip_tags($content)));
    }

    private function docxToHtml(string $filePath): string
    {
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Unable to open DOCX file.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($xml) || $xml === '') {
            return $this->plainTextToHtml('');
        }

        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));
        $clean = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return $this->plainTextToHtml($clean);
    }

    private function plainTextToHtml(string $text): string
    {
        $paragraphs = preg_split('/\n\s*\n/', trim($text)) ?: [];
        $html = array_map(
            fn (string $p): string => '<p>' . nl2br(e(trim($p))) . '</p>',
            array_filter($paragraphs, fn (string $p): bool => trim($p) !== '')
        );

        return implode("\n", $html);
    }

    private function filename(string $title, string $ext): string
    {
        $slug = \Illuminate\Support\Str::slug($title) ?: 'document';

        return $slug . '.' . $ext;
    }
}
