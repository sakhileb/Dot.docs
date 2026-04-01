<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFormField;
use Illuminate\Support\Collection;

class FormBuilderService
{
    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function normalizeOptions(string $optionsText): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $optionsText) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(function (string $line): array {
                [$value, $label] = array_pad(explode('|', $line, 2), 2, null);

                $value = trim((string) $value);
                $label = trim((string) ($label ?? $value));

                return [
                    'label' => $label,
                    'value' => $value,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, DocumentFormField>  $fields
     */
    public function buildFormMarkup(Document $document, Collection $fields): string
    {
        $fieldMarkup = $fields
            ->sortBy('sort_order')
            ->map(fn (DocumentFormField $field): string => $this->buildFieldMarkup($field))
            ->implode("\n");

        $title = e($document->title);

        return implode("\n", [
            '<section class="document-form-builder rounded-xl border border-gray-200 p-6" data-document-form-builder="true">',
            '    <div class="mb-4">',
            "        <h3>{$title} Form</h3>",
            '        <p>Generated with the built-in document form builder.</p>',
            '    </div>',
            '    <form class="space-y-4" method="post" action="#">',
            $fieldMarkup,
            '        <div>',
            '            <button type="submit">Submit</button>',
            '        </div>',
            '    </form>',
            '</section>',
        ]);
    }

    public function syncMarkupIntoContent(string $content, string $markup): string
    {
        $pattern = '/<section[^>]*data-document-form-builder="true"[^>]*>.*?<\/section>/si';

        if (preg_match($pattern, $content) === 1) {
            return preg_replace($pattern, $markup, $content, 1) ?? $content;
        }

        return trim($content)."\n\n".$markup;
    }

    private function buildFieldMarkup(DocumentFormField $field): string
    {
        $label = e($field->label);
        $name = e($field->name);
        $placeholder = $field->placeholder !== null && $field->placeholder !== ''
            ? ' placeholder="'.e($field->placeholder).'"'
            : '';
        $required = $field->is_required ? ' required' : '';
        $helpText = $field->help_text !== null && $field->help_text !== ''
            ? '        <p>'.e($field->help_text).'</p>'
            : null;

        $control = match ($field->field_type) {
            'textarea' => "        <textarea name=\"{$name}\"{$placeholder}{$required}></textarea>",
            'select' => $this->buildSelectMarkup($name, $field, $required),
            'radio' => $this->buildRadioMarkup($name, $field, $required),
            'checkbox' => "        <label><input type=\"checkbox\" name=\"{$name}\" value=\"1\"{$required}> {$label}</label>",
            default => "        <input type=\"".e($field->field_type)."\" name=\"{$name}\"{$placeholder}{$required}>",
        };

        $lines = [
            '        <div class="space-y-2">',
        ];

        if ($field->field_type !== 'checkbox') {
            $lines[] = "            <label for=\"{$name}\">{$label}".($field->is_required ? ' *' : '').'</label>';
        }

        $lines[] = $control;

        if ($helpText !== null) {
            $lines[] = $helpText;
        }

        $lines[] = '        </div>';

        return implode("\n", $lines);
    }

    private function buildSelectMarkup(string $name, DocumentFormField $field, string $required): string
    {
        $options = collect($field->options ?? [])
            ->map(function (array $option): string {
                $value = e((string) ($option['value'] ?? ''));
                $label = e((string) ($option['label'] ?? $value));

                return "            <option value=\"{$value}\">{$label}</option>";
            })
            ->implode("\n");

        return implode("\n", [
            "        <select name=\"{$name}\"{$required}>",
            '            <option value="">Select an option</option>',
            $options,
            '        </select>',
        ]);
    }

    private function buildRadioMarkup(string $name, DocumentFormField $field, string $required): string
    {
        return collect($field->options ?? [])
            ->map(function (array $option) use ($name, $required): string {
                $value = e((string) ($option['value'] ?? ''));
                $label = e((string) ($option['label'] ?? $value));

                return "        <label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\"{$required}> {$label}</label>";
            })
            ->implode("\n");
    }
}