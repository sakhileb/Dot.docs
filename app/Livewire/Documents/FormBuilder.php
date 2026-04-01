<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\DocumentFormField;
use App\Models\User;
use App\Services\FormBuilderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class FormBuilder extends Component
{
    public Document $document;

    public string $label = '';

    public string $fieldType = 'text';

    public string $placeholder = '';

    public string $helpText = '';

    public string $optionsText = '';

    public bool $isRequired = false;

    public function mount(Document $document): void
    {
        abort_unless($this->currentUser()->allTeams()->pluck('id')->contains($document->team_id), 403);

        $this->document = $document;
    }

    public function addField(): void
    {
        $validated = $this->validate([
            'label' => ['required', 'string', 'max:120'],
            'fieldType' => ['required', 'in:text,email,number,date,textarea,select,radio,checkbox'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'helpText' => ['nullable', 'string', 'max:255'],
            'optionsText' => ['nullable', 'string', 'max:2000'],
            'isRequired' => ['boolean'],
        ]);

        $service = app(FormBuilderService::class);
        $options = in_array($validated['fieldType'], ['select', 'radio'], true)
            ? $service->normalizeOptions($validated['optionsText'] ?? '')
            : [];

        if (in_array($validated['fieldType'], ['select', 'radio'], true) && $options === []) {
            $this->addError('optionsText', 'Provide at least one option for select and radio fields.');

            return;
        }

        DocumentFormField::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'label' => $validated['label'],
            'name' => $this->uniqueFieldName($validated['label']),
            'field_type' => $validated['fieldType'],
            'placeholder' => $validated['placeholder'] !== '' ? $validated['placeholder'] : null,
            'help_text' => $validated['helpText'] !== '' ? $validated['helpText'] : null,
            'is_required' => (bool) $validated['isRequired'],
            'options' => $options !== [] ? $options : null,
            'sort_order' => ((int) $this->document->formFields()->max('sort_order')) + 1,
        ]);

        $this->reset(['label', 'fieldType', 'placeholder', 'helpText', 'optionsText', 'isRequired']);
        $this->fieldType = 'text';

        $this->dispatch('notify', type: 'success', message: 'Form field added.');
    }

    public function deleteField(int $fieldId): void
    {
        $field = $this->document->formFields()->findOrFail($fieldId);
        $field->delete();

        $this->dispatch('notify', type: 'success', message: 'Form field removed.');
    }

    public function syncToDocument(): void
    {
        $fields = $this->document->formFields()->orderBy('sort_order')->get();

        if ($fields->isEmpty()) {
            $this->dispatch('notify', type: 'warning', message: 'Add at least one field before syncing.');

            return;
        }

        $service = app(FormBuilderService::class);
        $markup = $service->buildFormMarkup($this->document, $fields);
        $this->document->forceFill([
            'content' => $service->syncMarkupIntoContent($this->document->content ?? '', $markup),
        ])->save();

        $this->document->refresh();

        $this->dispatch('notify', type: 'success', message: 'Form synced into document content.');
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        $fields = $this->document->formFields()->orderBy('sort_order')->get();
        $previewMarkup = $fields->isEmpty()
            ? null
            : app(FormBuilderService::class)->buildFormMarkup($this->document, $fields);

        return view('livewire.documents.form-builder', [
            'fields' => $fields,
            'previewMarkup' => $previewMarkup,
        ]);
    }

    private function uniqueFieldName(string $label): string
    {
        $baseName = Str::of($label)->slug('_')->value();
        $baseName = $baseName !== '' ? $baseName : 'field';
        $name = $baseName;
        $counter = 2;

        while ($this->document->formFields()->where('name', $name)->exists()) {
            $name = $baseName.'_'.$counter;
            $counter++;
        }

        return $name;
    }
}