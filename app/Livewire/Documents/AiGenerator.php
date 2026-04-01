<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\User;
use App\Services\AiImageService;
use App\Services\AiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AiGenerator extends Component
{
    public string $type = 'general';

    public string $topic = '';

    public string $prompt = '';

    public string $tone = 'professional';

    public string $audience = 'General audience';

    public string $length = 'medium';

    public string $targetLanguage = 'English';

    public bool $isGenerating = false;

    public ?string $generatedTitle = null;

    public ?string $generatedContent = null;

    public ?string $seoTitle = null;

    public ?string $seoDescription = null;

    public ?string $seoKeywords = null;

    public string $imagePrompt = '';

    public string $imageSize = '1024x1024';

    public bool $isGeneratingImage = false;

    public ?string $generatedImageUrl = null;

    public function generate(): void
    {
        $this->validate([
            'type' => ['required', 'in:general,outline,blog,email,report,seo,translation'],
            'topic' => ['nullable', 'string', 'max:255'],
            'prompt' => ['nullable', 'string', 'max:4000'],
            'tone' => ['required', 'string', 'max:50'],
            'audience' => ['required', 'string', 'max:150'],
            'length' => ['required', 'in:short,medium,long'],
            'targetLanguage' => ['required', 'string', 'max:100'],
        ]);

        if (trim($this->topic) === '' && trim($this->prompt) === '') {
            $this->addError('prompt', 'Please provide a topic or a prompt.');

            return;
        }

        $this->isGenerating = true;

        try {
            /** @var AiService $service */
            $service = app(AiService::class);
            $result = $service->generateDocumentPackage([
                'type' => $this->type,
                'topic' => $this->topic,
                'prompt' => $this->prompt,
                'tone' => $this->tone,
                'audience' => $this->audience,
                'length' => $this->length,
                'target_language' => $this->targetLanguage,
            ]);

            $this->generatedTitle = $result['title'] ?: 'AI Generated Document';
            $this->generatedContent = $result['content'] ?: '';
            $this->seoTitle = $result['seo_title'] ?: '';
            $this->seoDescription = $result['seo_description'] ?: '';
            $this->seoKeywords = $result['seo_keywords'] ?: '';

            if ($this->imagePrompt === '' && $this->topic !== '') {
                $this->imagePrompt = $this->topic;
            }

            $this->dispatch('notify', type: 'success', message: 'Document generated. Review and save when ready.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'AI generation failed: '.$e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function generateImage(): void
    {
        $this->validate([
            'imagePrompt' => ['required', 'string', 'max:1000'],
            'imageSize' => ['required', 'in:256x256,512x512,1024x1024'],
        ]);

        $this->isGeneratingImage = true;

        try {
            /** @var AiImageService $service */
            $service = app(AiImageService::class);
            $this->generatedImageUrl = $service->generateImage($this->imagePrompt, $this->imageSize);

            $this->dispatch('notify', type: 'success', message: 'Image generated successfully.');
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'AI image generation failed: '.$e->getMessage());
        } finally {
            $this->isGeneratingImage = false;
        }
    }

    public function saveAsDocument(): void
    {
        if (! $this->generatedContent || ! $this->generatedTitle) {
            $this->dispatch('notify', type: 'warning', message: 'Generate content first.');

            return;
        }

        $teamId = $this->currentUser()->currentTeam?->id;

        if (! $teamId) {
            abort(403);
        }

        $seoBlock = "\n\n---\nSEO Title: {$this->seoTitle}\nSEO Description: {$this->seoDescription}\nSEO Keywords: {$this->seoKeywords}";

        $imageBlock = '';
        if ($this->generatedImageUrl) {
            $alt = trim($this->imagePrompt) !== '' ? $this->imagePrompt : 'AI generated image';
            $imageBlock = "\n\n![{$alt}]({$this->generatedImageUrl})";
        }

        $document = Document::create([
            'team_id' => $teamId,
            'user_id' => Auth::id(),
            'title' => $this->generatedTitle,
            'content' => $this->generatedContent.$imageBlock.$seoBlock,
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->redirectRoute('documents.edit', ['document' => $document->id], navigate: true);
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        return view('livewire.documents.ai-generator');
    }
}
