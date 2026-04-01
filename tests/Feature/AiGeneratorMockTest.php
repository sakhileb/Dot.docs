<?php

namespace Tests\Feature;

use App\Livewire\Documents\AiGenerator;
use App\Services\AiImageService;
use App\Services\AiService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiGeneratorMockTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_generator_uses_mocked_service_and_sets_generated_fields(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $mock = $this->mock(AiService::class);
        $mock->shouldReceive('generateDocumentPackage')
            ->once()
            ->andReturn([
                'title' => 'Mocked Title',
                'content' => 'Mocked content body',
                'seo_title' => 'Mocked SEO Title',
                'seo_description' => 'Mocked SEO Description',
                'seo_keywords' => 'mock, ai, test',
            ]);

        Livewire::actingAs($user)
            ->test(AiGenerator::class)
            ->set('topic', 'Testing with mocks')
            ->set('type', 'blog')
            ->call('generate')
            ->assertSet('generatedTitle', 'Mocked Title')
            ->assertSet('generatedContent', 'Mocked content body')
            ->assertSet('seoTitle', 'Mocked SEO Title')
            ->assertSet('isGenerating', false)
            ->assertDispatched('notify');
    }

    public function test_ai_generator_handles_service_failure_gracefully(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $mock = $this->mock(AiService::class);
        $mock->shouldReceive('generateDocumentPackage')
            ->once()
            ->andThrow(new \RuntimeException('Mocked AI failure'));

        Livewire::actingAs($user)
            ->test(AiGenerator::class)
            ->set('topic', 'Failure path test')
            ->call('generate')
            ->assertSet('isGenerating', false)
            ->assertDispatched('notify');
    }

    public function test_ai_image_generation_uses_mocked_service_and_sets_image_url(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $mock = $this->mock(AiImageService::class);
        $mock->shouldReceive('generateImage')
            ->once()
            ->with('A surreal city skyline at dawn', '1024x1024')
            ->andReturn('https://example.test/generated-image.png');

        Livewire::actingAs($user)
            ->test(AiGenerator::class)
            ->set('imagePrompt', 'A surreal city skyline at dawn')
            ->set('imageSize', '1024x1024')
            ->call('generateImage')
            ->assertSet('generatedImageUrl', 'https://example.test/generated-image.png')
            ->assertSet('isGeneratingImage', false)
            ->assertDispatched('notify');
    }

    public function test_ai_image_generation_handles_service_failure_gracefully(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $mock = $this->mock(AiImageService::class);
        $mock->shouldReceive('generateImage')
            ->once()
            ->andThrow(new \RuntimeException('Mocked image failure'));

        Livewire::actingAs($user)
            ->test(AiGenerator::class)
            ->set('imagePrompt', 'Generate a minimalist mountain icon')
            ->set('imageSize', '512x512')
            ->call('generateImage')
            ->assertSet('isGeneratingImage', false)
            ->assertDispatched('notify');
    }
}
