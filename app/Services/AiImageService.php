<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiImageService
{
    public function generateImage(string $prompt, string $size = '1024x1024'): string
    {
        $apiKey = config('services.openai.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        $model = (string) config('services.openai.image_model', 'gpt-image-1');
        $defaultSize = (string) config('services.openai.image_size', '1024x1024');
        $resolvedSize = in_array($size, ['256x256', '512x512', '1024x1024'], true) ? $size : $defaultSize;

        $response = \OpenAI::client($apiKey)->images()->create([
            'model' => $model,
            'prompt' => $prompt,
            'size' => $resolvedSize,
        ]);

        $first = $response->data[0] ?? null;

        if ($first && isset($first->url) && is_string($first->url) && $first->url !== '') {
            return $first->url;
        }

        if ($first && isset($first->b64_json) && is_string($first->b64_json) && $first->b64_json !== '') {
            $binary = base64_decode($first->b64_json, true);

            if ($binary === false) {
                throw new \RuntimeException('Invalid base64 image payload returned by AI provider.');
            }

            $path = 'generated-images/'.now()->format('Y/m').'/'.Str::uuid().'.png';
            Storage::disk('public')->put($path, $binary);

            return asset('storage/'.$path);
        }

        throw new \RuntimeException('AI image provider did not return a usable image URL.');
    }
}
