<?php

namespace Tests\Feature;

use App\Livewire\Documents\Transfer;
use App\Models\Document;
use App\Models\User;
use App\Services\CloudDocumentIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentCloudIntegrationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_drive_export_uploads_selected_documents(): void
    {
        config()->set('services.google_drive.access_token', 'google-token');
        config()->set('services.google_drive.folder_id', 'folder-123');

        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-file-1',
            ], 200),
        ]);

        [$user, $document] = $this->userAndDocument('Google Drive Export');

        app(CloudDocumentIntegrationService::class)->exportDocuments([$document], 'google_drive', 'txt', $user);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'googleapis.com/upload/drive/v3/files'));
    }

    public function test_google_drive_import_creates_document(): void
    {
        config()->set('services.google_drive.access_token', 'google-token');

        Http::fake([
            'https://www.googleapis.com/drive/v3/files/google-doc-1/export*' => Http::response("Plan intro\n\nMilestone one", 200),
            'https://www.googleapis.com/drive/v3/files/google-doc-1*' => Http::response([
                'id' => 'google-doc-1',
                'name' => 'Quarterly Plan',
                'mimeType' => 'application/vnd.google-apps.document',
            ], 200),
        ]);

        $user = User::factory()->withPersonalTeam()->create();

        Livewire::actingAs($user)
            ->test(Transfer::class)
            ->set('tab', 'import')
            ->set('cloudImportProvider', 'google_drive')
            ->set('cloudReference', 'https://docs.google.com/document/d/google-doc-1/edit')
            ->call('importFromCloud')
            ->assertHasNoErrors();

        $document = Document::query()->latest('id')->firstOrFail();

        $this->assertSame('Quarterly Plan', $document->title);
        $this->assertStringContainsString('Plan intro', (string) $document->content);
    }

    public function test_dropbox_export_uploads_selected_documents(): void
    {
        config()->set('services.dropbox.access_token', 'dropbox-token');
        config()->set('services.dropbox.folder_path', '/DotDocs');

        Http::fake([
            'https://content.dropboxapi.com/2/files/upload' => Http::response([
                'name' => 'dropbox-export.txt',
            ], 200),
        ]);

        [$user, $document] = $this->userAndDocument('Dropbox Export');

        app(CloudDocumentIntegrationService::class)->exportDocuments([$document], 'dropbox', 'txt', $user);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'content.dropboxapi.com/2/files/upload'));
    }

    public function test_dropbox_shared_link_import_creates_document(): void
    {
        Http::fake([
            'https://www.dropbox.com/s/test/shared-brief.txt?dl=1' => Http::response('Shared brief contents', 200, [
                'Content-Disposition' => 'attachment; filename="shared-brief.txt"',
            ]),
        ]);

        $user = User::factory()->withPersonalTeam()->create();

        Livewire::actingAs($user)
            ->test(Transfer::class)
            ->set('tab', 'import')
            ->set('cloudImportProvider', 'dropbox')
            ->set('cloudReference', 'https://www.dropbox.com/s/test/shared-brief.txt?dl=0')
            ->call('importFromCloud')
            ->assertHasNoErrors();

        $document = Document::query()->latest('id')->firstOrFail();

        $this->assertSame('shared-brief', $document->title);
        $this->assertStringContainsString('Shared brief contents', (string) $document->content);
    }

    public function test_onedrive_export_uploads_selected_documents(): void
    {
        config()->set('services.onedrive.access_token', 'onedrive-token');
        config()->set('services.onedrive.folder_path', 'DotDocs');

        Http::fake([
            'https://graph.microsoft.com/v1.0/me/drive/root:/DotDocs/*:/content' => Http::response([
                'id' => 'onedrive-file-1',
            ], 201),
        ]);

        [$user, $document] = $this->userAndDocument('OneDrive Export');

        app(CloudDocumentIntegrationService::class)->exportDocuments([$document], 'onedrive', 'txt', $user);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.microsoft.com/v1.0/me/drive/root:/DotDocs/'));
    }

    public function test_onedrive_shared_link_import_creates_document(): void
    {
        config()->set('services.onedrive.access_token', 'onedrive-token');

        $shareUrl = 'https://onedrive.live.com/?cid=123&id=ABC';
        $encodedShare = 'u!'.rtrim(strtr(base64_encode($shareUrl), '+/', '-_'), '=');

        Http::fake([
            "https://graph.microsoft.com/v1.0/shares/{$encodedShare}/driveItem*" => Http::response([
                'name' => 'onedrive-notes.txt',
                '@microsoft.graph.downloadUrl' => 'https://download.onedrive.test/file.txt',
            ], 200),
            'https://download.onedrive.test/file.txt' => Http::response('OneDrive import body', 200),
        ]);

        $user = User::factory()->withPersonalTeam()->create();

        Livewire::actingAs($user)
            ->test(Transfer::class)
            ->set('tab', 'import')
            ->set('cloudImportProvider', 'onedrive')
            ->set('cloudReference', $shareUrl)
            ->call('importFromCloud')
            ->assertHasNoErrors();

        $document = Document::query()->latest('id')->firstOrFail();

        $this->assertSame('onedrive-notes', $document->title);
        $this->assertStringContainsString('OneDrive import body', (string) $document->content);
    }

    /**
     * @return array{0: User, 1: Document}
     */
    private function userAndDocument(string $title): array
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => $title,
            'content' => '<p>Cloud export body</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        return [$user, $document];
    }
}