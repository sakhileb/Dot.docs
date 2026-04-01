<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\User;
use App\Services\MailMergeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MailMerge extends Component
{
    public Document $document;

    public string $templateContent = '';

    public string $recipientJson = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $mergedDocuments = [];

    public function mount(Document $document): void
    {
        abort_unless($this->currentUser()->allTeams()->pluck('id')->contains($document->team_id), 403);

        $this->document = $document;
        $this->templateContent = $document->content ?? '';
    }

    public function previewMerge(): void
    {
        $validated = $this->validate([
            'templateContent' => ['required', 'string'],
            'recipientJson' => ['required', 'string', 'min:2'],
        ]);

        $service = app(MailMergeService::class);
        $recipients = $service->parseRecipients($validated['recipientJson']);

        if ($recipients === []) {
            $this->addError('recipientJson', 'Provide a valid JSON array of recipients or an object with a recipients key.');

            return;
        }

        $this->mergedDocuments = $service->buildMergedDocuments($validated['templateContent'], $recipients);
        $this->dispatch('notify', type: 'success', message: 'Mail merge preview generated.');
    }

    public function saveMergedDocuments(): void
    {
        if ($this->mergedDocuments === []) {
            $this->dispatch('notify', type: 'warning', message: 'Generate a preview first.');

            return;
        }

        $created = 0;

        foreach ($this->mergedDocuments as $merged) {
            $recipient = $merged['recipient'] ?? [];
            $recipientName = is_array($recipient) ? (string) ($recipient['name'] ?? $recipient['full_name'] ?? $recipient['email'] ?? 'Recipient') : 'Recipient';

            Document::create([
                'team_id' => $this->document->team_id,
                'user_id' => Auth::id(),
                'title' => $this->document->title.' - '.$recipientName,
                'content' => (string) ($merged['content'] ?? ''),
                'version' => 1,
                'status' => 'draft',
                'is_archived' => false,
            ]);

            $created++;
        }

        $this->dispatch('notify', type: 'success', message: "Created {$created} merged document(s).");
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        return view('livewire.documents.mail-merge');
    }
}
