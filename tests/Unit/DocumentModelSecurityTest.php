<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentModelSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_content_is_encrypted_at_rest_and_decrypted_on_read(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $document = Document::query()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'title' => 'Security Test',
            'content' => '<p onclick="alert(1)">Hello <strong>world</strong></p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $raw = $document->getRawOriginal('content');

        $this->assertNotNull($raw);
        $this->assertStringNotContainsString('Hello', (string) $raw);
        $this->assertStringNotContainsString('onclick', $document->content ?? '');
        $this->assertSame('<p>Hello <strong>world</strong></p>', $document->content);
    }
}
