<?php

namespace App\Livewire\Documents;

use App\Models\AiSuggestion;
use App\Models\Document;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AiAssistant extends Component
{
    public Document $document;

    public string $selectedText = '';

    public ?string $aiResponse = null;

    public ?string $currentOperation = null;

    public bool $isLoading = false;

    public string $toneSelection = 'professional';

    public string $citationStyle = 'APA';

    public array $chat = [];

    public ?int $pendingSuggestionId = null;

    public int $pendingProgress = 0;

    public ?string $pendingStatus = null;

    public function mount(Document $document): void
    {
        $this->document = $document;
    }

    #[On('text-selected')]
    public function handleTextSelected(string $text): void
    {
        $this->selectedText = $text;
        $this->aiResponse = null;
        $this->currentOperation = null;
    }

    public function completeText(): void
    {
        $this->executeAiOperation('completion', function () {
            $aiService = app(AiService::class);
            return $aiService->completeText($this->selectedText, $this->document, Auth::user());
        });
    }

    public function paraphrase(): void
    {
        $this->executeAiOperation('paraphrase', function () {
            $aiService = app(AiService::class);
            return $aiService->paraphraseText($this->selectedText, $this->document, Auth::user());
        });
    }

    public function adjustTone(): void
    {
        $this->executeAiOperation('tone_adjustment', function () {
            $aiService = app(AiService::class);
            return $aiService->adjustTone($this->selectedText, $this->toneSelection, $this->document, Auth::user());
        });
    }

    public function checkGrammar(): void
    {
        $this->executeAiOperation('grammar_check', function () {
            $aiService = app(AiService::class);
            $result = $aiService->checkGrammar($this->selectedText, $this->document, Auth::user());
            return $result['corrected'] ?? 'No corrections needed';
        });
    }

    public function summarize(): void
    {
        $this->executeAiOperation('summarization', function () {
            $aiService = app(AiService::class);
            return $aiService->summarizeText($this->selectedText, $this->document, Auth::user());
        });
    }

    public function expandText(): void
    {
        $this->executeAiOperation('expansion', function () {
            $aiService = app(AiService::class);
            return $aiService->expandText($this->selectedText, $this->document, Auth::user());
        });
    }

    public function shortenText(): void
    {
        $this->executeAiOperation('shortening', function () {
            $aiService = app(AiService::class);
            return $aiService->shortenText($this->selectedText, $this->document, Auth::user());
        });
    }

    public function analyzeReadability(): void
    {
        $this->executeAiOperation('readability_analysis', function () {
            $aiService = app(AiService::class);
            $result = $aiService->analyzeReadability($this->selectedText, $this->document, Auth::user());
            return sprintf(
                "Readability Score: %d/100\nReading Level: %s\nSuggestions: %s",
                $result['score'] ?? 50,
                $result['reading_level'] ?? 'college',
                implode(", ", $result['suggestions'] ?? [])
            );
        });
    }

    public function autoFormatRawText(): void
    {
        $this->queueAiOperation('auto_formatting');
    }

    public function improveContent(): void
    {
        $this->queueAiOperation('content_improvement');
    }

    public function optimizeStructure(): void
    {
        $this->queueAiOperation('structure_optimization');
    }

    public function extractKeyPhrases(): void
    {
        $this->queueAiOperation('key_phrase_extraction');
    }

    public function suggestTablesAndCharts(): void
    {
        $this->queueAiOperation('table_chart_suggestion');
    }

    public function generateCitations(): void
    {
        $this->queueAiOperation('citation_generation', ['style' => $this->citationStyle]);
    }

    public function improveReadability(): void
    {
        $this->queueAiOperation('readability_improvement');
    }

    public function checkPlagiarism(): void
    {
        $this->executeAiOperation('plagiarism_check', function () {
            $aiService = app(AiService::class);
            $result = $aiService->checkPlagiarism($this->selectedText, $this->document, Auth::user());

            return sprintf(
                "Plagiarism Risk Score: %d/100\nLikely Originality: %s\nFlagged Phrases: %s\nRecommendations: %s\nSummary: %s",
                $result['risk_score'] ?? 0,
                $result['likely_originality'] ?? 'medium',
                implode('; ', $result['flagged_phrases'] ?? []),
                implode('; ', $result['recommendations'] ?? []),
                $result['summary'] ?? ''
            );
        });
    }

    public function pollSuggestionStatus(): void
    {
        if (! $this->pendingSuggestionId) {
            return;
        }

        $suggestion = AiSuggestion::query()->find($this->pendingSuggestionId);

        if (! $suggestion) {
            $this->pendingSuggestionId = null;
            $this->pendingProgress = 0;
            $this->pendingStatus = null;

            return;
        }

        $this->pendingProgress = (int) $suggestion->progress;
        $this->pendingStatus = $suggestion->status;

        if ($suggestion->status === 'completed') {
            $this->aiResponse = $suggestion->response;
            $this->addChatMessage('assistant', (string) $suggestion->response);
            $this->pendingSuggestionId = null;
            $this->pendingProgress = 0;
            $this->pendingStatus = null;
            $this->isLoading = false;
            $this->dispatch('notify', type: 'success', message: 'AI task completed.');
        }

        if ($suggestion->status === 'failed') {
            $this->pendingSuggestionId = null;
            $this->pendingProgress = 0;
            $this->pendingStatus = null;
            $this->isLoading = false;
            $this->dispatch('notify', type: 'error', message: $suggestion->error_message ?: 'AI task failed.');
        }
    }

    public function acceptSuggestion(): void
    {
        if ($this->aiResponse) {
            $this->dispatch('ai-suggestion-accepted', suggestion: $this->aiResponse);
            $this->addChatMessage('assistant', $this->aiResponse, true);
            $this->aiResponse = null;
            $this->selectedText = '';
            $this->dispatch('notify', type: 'success', message: 'Suggestion accepted!');
        }
    }

    public function rejectSuggestion(): void
    {
        $this->aiResponse = null;
        $this->selectedText = '';
        $this->dispatch('notify', type: 'info', message: 'Suggestion discarded.');
    }

    private function executeAiOperation(string $operation, callable $callback): void
    {
        if (! $this->selectedText) {
            $this->dispatch('notify', type: 'warning', message: 'Please select text first.');
            return;
        }

        $this->isLoading = true;
        $this->currentOperation = $operation;

        try {
            $response = $callback();
            $this->aiResponse = $response;
            $this->addChatMessage('user', $this->selectedText);
            $this->addChatMessage('assistant', $response);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'AI service error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    private function addChatMessage(string $role, string $message, bool $accepted = false): void
    {
        $this->chat[] = [
            'role' => $role,
            'message' => $message,
            'accepted' => $accepted,
            'timestamp' => now()->format('H:i'),
        ];

        // Keep chat history to last 20 messages
        if (count($this->chat) > 20) {
            $this->chat = array_slice($this->chat, -20);
        }
    }

    public function clearChat(): void
    {
        $this->chat = [];
        $this->aiResponse = null;
        $this->selectedText = '';
        $this->pendingSuggestionId = null;
        $this->pendingProgress = 0;
        $this->pendingStatus = null;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function queueAiOperation(string $operation, array $metadata = []): void
    {
        if (! $this->selectedText) {
            $this->dispatch('notify', type: 'warning', message: 'Please select text first.');

            return;
        }

        if ($this->pendingSuggestionId) {
            $this->dispatch('notify', type: 'info', message: 'A queued AI task is still running.');

            return;
        }

        try {
            $aiService = app(AiService::class);
            $suggestion = $aiService->queueOperation(
                $this->document,
                $this->currentUser(),
                $operation,
                $this->selectedText,
                $metadata
            );

            \App\Jobs\ProcessAiSuggestionJob::dispatch($suggestion->id)->onQueue(env('AI_QUEUE_NAME', 'ai'));

            $this->pendingSuggestionId = $suggestion->id;
            $this->pendingProgress = 0;
            $this->pendingStatus = 'pending';
            $this->currentOperation = $operation;
            $this->isLoading = true;
            $this->addChatMessage('user', $this->selectedText);
            $this->dispatch('notify', type: 'info', message: 'AI task queued. Processing in background...');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to queue AI task: ' . $e->getMessage());
        }
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        return view('livewire.documents.ai-assistant');
    }
}

