<?php

namespace App\Services;

use App\Models\AiSuggestion;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use OpenAI\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AiService
{
    private Client $client;

    private string $model;

    public function __construct()
    {
        $this->client = \OpenAI::client(config('services.openai.api_key'));
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Complete text - continue writing based on selected text
     */
    public function completeText(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Based on the following text, continue writing naturally and coherently. Keep the same tone and style.\n\nText: {$selectedText}\n\nContinuation:";

        return $this->callAi($prompt, $document, $user, 'completion');
    }

    /**
     * Paraphrase text with different wording while keeping meaning
     */
    public function paraphraseText(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Paraphrase the following text using different words and sentence structure. Keep the meaning and tone exactly the same, just change how it's expressed.\n\nOriginal: {$selectedText}\n\nParaphrase:";

        return $this->callAi($prompt, $document, $user, 'paraphrase');
    }

    /**
     * Change tone of text - professional, casual, friendly, formal
     */
    public function adjustTone(string $selectedText, string $tone, Document $document, User $user): string
    {
        $tones = ['professional', 'casual', 'friendly', 'formal', 'energetic', 'sad', 'happy'];
        $toneDesc = in_array($tone, $tones) ? $tone : 'professional';

        $prompt = "Rewrite the following text in a {$toneDesc} tone. Keep all the information but change how it's expressed to match the requested tone.\n\nOriginal: {$selectedText}\n\nRewritten:";

        return $this->callAi($prompt, $document, $user, 'tone_adjustment', ['tone' => $toneDesc]);
    }

    /**
     * Check grammar and provide corrections
     */
    public function checkGrammar(string $selectedText, Document $document, User $user): array
    {
        $prompt = "Analyze the following text for grammar, spelling, and punctuation errors. Provide a corrected version and list any errors found. Return as JSON with 'corrected' and 'errors' keys.\n\nText: {$selectedText}\n\nResponse (JSON):";

        $response = $this->callAi($prompt, $document, $user, 'grammar_check');

        try {
            return json_decode($response, true) ?? ['corrected' => $response, 'errors' => []];
        } catch (\Exception $e) {
            return ['corrected' => $response, 'errors' => []];
        }
    }

    /**
     * Summarize text
     */
    public function summarizeText(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Summarize the following text in 2-3 sentences, capturing the main points.\n\nText: {$selectedText}\n\nSummary:";

        return $this->callAi($prompt, $document, $user, 'summarization');
    }

    /**
     * Get readability score and suggestions
     */
    public function analyzeReadability(string $content, Document $document, User $user): array
    {
        $prompt = "Analyze the readability of the following text. Provide a score (0-100), reading level (elementary, high school, college, professional), and specific suggestions for improvement.\n\nText: {$content}\n\nReturn as JSON with 'score', 'reading_level', and 'suggestions' keys.\n\nResponse (JSON):";

        $response = $this->callAi($prompt, $document, $user, 'readability_analysis');

        try {
            $data = json_decode($response, true);
            return $data ?? ['score' => 50, 'reading_level' => 'college', 'suggestions' => []];
        } catch (\Exception $e) {
            return ['score' => 50, 'reading_level' => 'college', 'suggestions' => []];
        }
    }

    /**
     * Expand selected text with more details
     */
    public function expandText(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Expand the following text by adding more details, examples, or explanation while maintaining the original meaning and tone. Make it 2-3x longer.\n\nOriginal: {$selectedText}\n\nExpanded:";

        return $this->callAi($prompt, $document, $user, 'expansion');
    }

    /**
     * Shorten text while keeping main idea
     */
    public function shortenText(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Shorten the following text to about half its length while keeping all important information. Remove redundancy and keep it concise.\n\nOriginal: {$selectedText}\n\nShortened:";

        return $this->callAi($prompt, $document, $user, 'shortening');
    }

    /**
     * Convert raw text into properly structured and formatted markdown.
     */
    public function autoFormatRawText(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Format the following raw text into clean, readable markdown. Add sensible headings, bullet points, spacing, and emphasis where appropriate. Do not change the core meaning.\n\nText:\n{$selectedText}\n\nFormatted markdown:";

        return $this->callAi($prompt, $document, $user, 'auto_formatting');
    }

    /**
     * Provide improved version of content with clarity and impact.
     */
    public function improveContent(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Improve the following text for clarity, conciseness, flow, and impact. Preserve intent, facts, and tone as much as possible.\n\nOriginal:\n{$selectedText}\n\nImproved:";

        return $this->callAi($prompt, $document, $user, 'content_improvement');
    }

    /**
     * Optimize headings and section structure.
     */
    public function optimizeStructure(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Reorganize the following text into a better heading and section structure. Use clear H2/H3 style headings and logical grouping. Return markdown only.\n\nText:\n{$selectedText}\n\nOptimized structure:";

        return $this->callAi($prompt, $document, $user, 'structure_optimization');
    }

    /**
     * Extract key phrases and important entities.
     */
    public function extractKeyPhrases(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Extract the most important key phrases from the following text. Return exactly 8-15 items as a bullet list.\n\nText:\n{$selectedText}\n\nKey phrases:";

        return $this->callAi($prompt, $document, $user, 'key_phrase_extraction');
    }

    /**
     * Suggest potential tables and charts from textual data.
     */
    public function suggestTablesAndCharts(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Analyze the following content and suggest useful tables and charts that could represent the information. For each suggestion include: type, title, columns/axes, and why it helps.\n\nText:\n{$selectedText}\n\nSuggestions:";

        return $this->callAi($prompt, $document, $user, 'table_chart_suggestion');
    }

    /**
     * Generate citations and references in requested style.
     */
    public function generateCitations(string $selectedText, string $style, Document $document, User $user): string
    {
        $citationStyle = in_array($style, ['APA', 'MLA', 'Chicago', 'Harvard'], true) ? $style : 'APA';

        $prompt = "Generate citation placeholders and reference entries for claims in the following text using {$citationStyle} style. If source details are missing, mark placeholders clearly. Include a 'References' section.\n\nText:\n{$selectedText}\n\nCitations and references:";

        return $this->callAi($prompt, $document, $user, 'citation_generation', ['style' => $citationStyle]);
    }

    /**
     * Rewrite content to improve readability while preserving meaning.
     */
    public function improveReadability(string $selectedText, Document $document, User $user): string
    {
        $prompt = "Rewrite the following text to improve readability: shorter sentences, clearer wording, better transitions, and active voice where appropriate. Preserve meaning.\n\nText:\n{$selectedText}\n\nReadability-improved version:";

        return $this->callAi($prompt, $document, $user, 'readability_improvement');
    }

    /**
     * Analyze text for potential plagiarism risk and originality signals.
     */
    public function checkPlagiarism(string $selectedText, Document $document, User $user): array
    {
        $prompt = "Analyze the following text for potential plagiarism risk using linguistic heuristics only (no external web lookup). Return strict JSON with keys: risk_score (0-100), likely_originality (low|medium|high), flagged_phrases (array of short strings), recommendations (array of short strings), summary (string).\n\nText:\n{$selectedText}\n\nResponse (JSON):";

        $response = $this->callAi($prompt, $document, $user, 'plagiarism_check');

        try {
            $decoded = json_decode($response, true);

            if (! is_array($decoded)) {
                throw new \RuntimeException('Invalid plagiarism response payload.');
            }

            return [
                'risk_score' => (int) ($decoded['risk_score'] ?? 0),
                'likely_originality' => (string) ($decoded['likely_originality'] ?? 'medium'),
                'flagged_phrases' => array_values($decoded['flagged_phrases'] ?? []),
                'recommendations' => array_values($decoded['recommendations'] ?? []),
                'summary' => (string) ($decoded['summary'] ?? ''),
            ];
        } catch (\Throwable) {
            return [
                'risk_score' => 0,
                'likely_originality' => 'medium',
                'flagged_phrases' => [],
                'recommendations' => [],
                'summary' => (string) $response,
            ];
        }
    }

    /**
     * Queue a long-running AI operation and return the tracked suggestion record.
     *
     * @param array<string, mixed> $metadata
     */
    public function queueOperation(
        Document $document,
        User $user,
        string $operation,
        string $input,
        array $metadata = []
    ): AiSuggestion {
        $requestHash = $this->makeRequestHash($operation, $input, $metadata);

        return AiSuggestion::create([
            'document_id' => $document->id,
            'team_id' => $document->team_id,
            'user_id' => $user->id,
            'prompt' => $input,
            'operation' => $operation,
            'status' => 'pending',
            'progress' => 0,
            'request_hash' => $requestHash,
            'is_cached' => false,
            'queued_at' => now(),
            'provider' => 'openai',
            'model' => $this->model,
            'metadata' => $metadata,
        ]);
    }

    public function processQueuedSuggestion(AiSuggestion $suggestion): void
    {
        $metadata = is_array($suggestion->metadata) ? $suggestion->metadata : [];
        $operation = $suggestion->operation ?: 'content_improvement';

        $suggestion->update([
            'status' => 'processing',
            'progress' => 15,
            'started_at' => now(),
        ]);

        $user = $suggestion->user;
        if ($user) {
            $this->enforceRateLimit($user);
        }

        $cacheKey = $this->cacheKeyFromHash($suggestion->request_hash ?: $this->makeRequestHash($operation, $suggestion->prompt, $metadata));
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && isset($cached['response'])) {
            $suggestion->update([
                'response' => (string) $cached['response'],
                'status' => 'completed',
                'progress' => 100,
                'is_cached' => true,
                'token_usage' => $cached['token_usage'] ?? null,
                'completed_at' => now(),
                'processed_at' => now(),
            ]);

            return;
        }

        $suggestion->update(['progress' => 45]);

        $prompt = $this->buildQueuedPrompt($operation, $suggestion->prompt, $metadata);
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful writing assistant focused on practical output quality improvements.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 900,
        ]);

        $content = $response->choices[0]->message->content;
        $tokenUsage = $response->usage->totalTokens ?? null;

        $suggestion->update([
            'response' => $content,
            'status' => 'completed',
            'progress' => 100,
            'is_cached' => false,
            'token_usage' => $tokenUsage,
            'completed_at' => now(),
            'processed_at' => now(),
        ]);

        $ttlMinutes = max((int) env('AI_CACHE_TTL_MINUTES', 60), 1);
        Cache::put($cacheKey, [
            'response' => $content,
            'token_usage' => $tokenUsage,
        ], now()->addMinutes($ttlMinutes));
    }

    /**
     * Generate a full document package for Phase 4.2 wizard.
     *
     * @param array<string, string> $input
     * @return array<string, string>
     */
    public function generateDocumentPackage(array $input): array
    {
        $type = $input['type'] ?? 'general';
        $topic = trim($input['topic'] ?? '');
        $prompt = trim($input['prompt'] ?? '');
        $tone = $input['tone'] ?? 'professional';
        $audience = $input['audience'] ?? 'General audience';
        $length = $input['length'] ?? 'medium';
        $targetLanguage = $input['target_language'] ?? 'English';

        $typeInstruction = match ($type) {
            'outline' => 'Generate a detailed structured outline with sections, subsections, and bullet points.',
            'blog' => 'Generate a polished blog post/article with a strong hook, headings, and a concise conclusion.',
            'email' => 'Generate a professional email or letter format with subject and clear call-to-action.',
            'report' => 'Generate a business report/proposal with executive summary, findings, and recommendations.',
            'seo' => 'Generate SEO-focused content with keyword-aware headings and metadata-friendly structure.',
            'translation' => 'Generate content translated into the requested target language while preserving meaning.',
            default => 'Generate a high-quality general-purpose document.',
        };

        $lengthInstruction = match ($length) {
            'short' => 'Keep it concise: about 250-400 words.',
            'long' => 'Make it comprehensive: about 1000-1400 words.',
            default => 'Keep it balanced: about 600-900 words.',
        };

        $task = $prompt !== '' ? $prompt : $topic;

        $fullPrompt = "You are an expert AI document generator.\n"
            . "Task: {$task}\n"
            . "Document type: {$type}\n"
            . "Audience: {$audience}\n"
            . "Tone: {$tone}\n"
            . "Target language: {$targetLanguage}\n"
            . "Instructions: {$typeInstruction}\n"
            . "Length: {$lengthInstruction}\n\n"
            . "Return strict JSON with keys:\n"
            . "title (string),\n"
            . "content (string, markdown-compatible),\n"
            . "seo_title (string),\n"
            . "seo_description (string),\n"
            . "seo_keywords (string, comma-separated).";

        $raw = $this->chatRaw($fullPrompt, 1600, 0.7);
        $decoded = $this->decodeJsonPayload($raw);

        return [
            'title' => (string) ($decoded['title'] ?? 'AI Generated Document'),
            'content' => (string) ($decoded['content'] ?? $raw),
            'seo_title' => (string) ($decoded['seo_title'] ?? ''),
            'seo_description' => (string) ($decoded['seo_description'] ?? ''),
            'seo_keywords' => (string) ($decoded['seo_keywords'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonPayload(string $raw): array
    {
        $trimmed = trim($raw);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function chatRaw(string $prompt, int $maxTokens = 900, float $temperature = 0.7): string
    {
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert writing and document generation assistant. Follow the user format requirements exactly.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        return $response->choices[0]->message->content;
    }

    /**
     * Make internal API call to OpenAI, store as AI suggestion
     */
    private function callAi(
        string $prompt,
        Document $document,
        User $user,
        string $suggestionType,
        ?array $metadata = null
    ): string {
        try {
            $this->enforceRateLimit($user);

            $requestHash = $this->makeRequestHash($suggestionType, $prompt, $metadata ?? []);
            $cacheKey = $this->cacheKeyFromHash($requestHash);
            $cached = Cache::get($cacheKey);

            if (is_array($cached) && isset($cached['response'])) {
                AiSuggestion::create([
                    'document_id' => $document->id,
                    'user_id' => $user->id,
                    'team_id' => $document->team_id,
                    'prompt' => $prompt,
                    'operation' => $suggestionType,
                    'response' => (string) $cached['response'],
                    'status' => 'completed',
                    'progress' => 100,
                    'request_hash' => $requestHash,
                    'is_cached' => true,
                    'provider' => 'openai',
                    'model' => $this->model,
                    'token_usage' => $cached['token_usage'] ?? null,
                    'completed_at' => now(),
                    'processed_at' => now(),
                    'metadata' => array_merge(['type' => $suggestionType], $metadata ?? []),
                ]);

                return (string) $cached['response'];
            }

            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful writing assistant. Provide concise, practical suggestions that improve writing quality while maintaining original intent.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            $content = $response->choices[0]->message->content;

            // Log AI suggestion
            AiSuggestion::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'team_id' => $document->team_id,
                'prompt' => $prompt,
                'operation' => $suggestionType,
                'response' => $content,
                'status' => 'completed',
                'progress' => 100,
                'request_hash' => $requestHash,
                'is_cached' => false,
                'provider' => 'openai',
                'model' => $this->model,
                'token_usage' => $response->usage->totalTokens,
                'completed_at' => now(),
                'processed_at' => now(),
                'metadata' => array_merge(['type' => $suggestionType], $metadata ?? []),
            ]);

            $ttlMinutes = max((int) env('AI_CACHE_TTL_MINUTES', 60), 1);
            Cache::put($cacheKey, [
                'response' => $content,
                'token_usage' => $response->usage->totalTokens,
            ], now()->addMinutes($ttlMinutes));

            return $content;
        } catch (\Exception $e) {
            Log::error('AI Service Error', ['error' => $e->getMessage(), 'prompt' => $prompt]);

            // Log failed suggestion
            AiSuggestion::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'team_id' => $document->team_id,
                'prompt' => $prompt,
                'operation' => $suggestionType,
                'response' => null,
                'status' => 'failed',
                'progress' => 100,
                'provider' => 'openai',
                'model' => $this->model,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
                'processed_at' => now(),
                'metadata' => ['type' => $suggestionType],
            ]);

            throw $e;
        }
    }

    private function buildQueuedPrompt(string $operation, string $input, array $metadata = []): string
    {
        return match ($operation) {
            'auto_formatting' => "Format the following raw text into clean, readable markdown with sensible headings, spacing, and lists.\n\nText:\n{$input}",
            'content_improvement' => "Improve the following text for clarity, conciseness, and impact while preserving meaning.\n\nText:\n{$input}",
            'structure_optimization' => "Reorganize the following text into clear sections with strong headings. Return markdown only.\n\nText:\n{$input}",
            'key_phrase_extraction' => "Extract 8-15 key phrases from the following text as bullets.\n\nText:\n{$input}",
            'table_chart_suggestion' => "Suggest tables/charts from the following content with reasons and fields/axes.\n\nText:\n{$input}",
            'citation_generation' => "Generate citation placeholders and references for this text in " . ($metadata['style'] ?? 'APA') . " style.\n\nText:\n{$input}",
            'readability_improvement' => "Rewrite this content for better readability without changing core meaning.\n\nText:\n{$input}",
            default => $input,
        };
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function makeRequestHash(string $operation, string $prompt, array $metadata = []): string
    {
        return hash('sha256', json_encode([
            'operation' => $operation,
            'prompt' => $prompt,
            'metadata' => $metadata,
            'model' => $this->model,
        ], JSON_THROW_ON_ERROR));
    }

    private function cacheKeyFromHash(string $hash): string
    {
        return 'ai_response:' . $hash;
    }

    private function enforceRateLimit(User $user): void
    {
        $maxAttempts = max((int) env('AI_RATE_LIMIT_PER_MINUTE', 20), 1);
        $key = 'ai_rate_limit:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new \RuntimeException('AI rate limit exceeded. Please wait before retrying.');
        }

        RateLimiter::hit($key, 60);
    }
}
