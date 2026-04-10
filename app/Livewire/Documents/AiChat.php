<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Services\AiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AiChat extends Component
{
    use AuthorizesRequests;

    public Document $document;

    public string $message = '';

    public array $history = [];

    public bool $loading = false;

    public bool $open = false;

    public function mount(Document $document): void
    {
        $this->document = $document;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function send(): void
    {
        $this->authorize('view', $this->document);

        $message = trim($this->message);
        if ($message === '') {
            return;
        }

        $ai = app(AiService::class);

        if (! $ai->checkRateLimit(auth()->id())) {
            $this->history[] = [
                'role'    => 'assistant',
                'content' => 'Rate limit reached. You can make 20 AI requests per hour.',
            ];
            $this->message = '';
            return;
        }

        $this->history[] = ['role' => 'user', 'content' => $message];
        $this->message   = '';
        $this->loading   = true;

        try {
            $html = $this->document->fresh()->content ?? '';

            // Pass all previous turns except the one just appended (it'll be re-added inside chat())
            $previousHistory = array_slice($this->history, 0, -1);

            $reply = $ai->chat($message, $html, $previousHistory);

            $this->history[] = ['role' => 'assistant', 'content' => $reply];
        } catch (\Throwable $e) {
            $this->history[] = [
                'role'    => 'assistant',
                'content' => 'Error: ' . $e->getMessage(),
            ];
        } finally {
            $this->loading = false;
        }

        // Keep last 20 turns to avoid token bloat
        if (count($this->history) > 20) {
            $this->history = array_slice($this->history, -20);
        }
    }

    public function clearHistory(): void
    {
        $this->history = [];
    }

    public function render()
    {
        return view('livewire.documents.ai-chat');
    }
}

