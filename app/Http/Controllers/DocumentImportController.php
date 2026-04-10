<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use League\CommonMark\CommonMarkConverter;
use PhpOffice\PhpWord\IOFactory;

class DocumentImportController extends Controller
{
    public function store(Request $request, string $uuid): RedirectResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $document);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:docx,md,txt,markdown',
            ],
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $content = match ($extension) {
            'docx'              => $this->parseDocx($file->getRealPath()),
            'md', 'markdown','txt' => $this->parseMarkdown(file_get_contents($file->getRealPath())),
            default             => abort(422, 'Unsupported file type.'),
        };

        $document->update(['content' => $content]);

        return redirect()
            ->route('documents.edit', $document->uuid)
            ->with('status', 'File imported successfully.');
    }

    private function parseDocx(string $path): string
    {
        $phpWord  = IOFactory::load($path);
        $sections = $phpWord->getSections();
        $html     = '';

        foreach ($sections as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    $line = '';
                    foreach ($element->getElements() as $textEl) {
                        if (method_exists($textEl, 'getText')) {
                            $line .= htmlspecialchars($textEl->getText());
                        }
                    }
                    $html .= "<p>{$line}</p>";
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $html .= '<p>' . htmlspecialchars($element->getText()) . '</p>';
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Title) {
                    $level = $element->getDepth() ?: 1;
                    $html .= "<h{$level}>" . htmlspecialchars($element->getText()) . "</h{$level}>";
                }
            }
        }

        return $html;
    }

    private function parseMarkdown(string $markdown): string
    {
        $converter = new CommonMarkConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convert($markdown)->getContent();
    }
}

