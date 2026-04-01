<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyRoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withPersonalTeam()->create();
    }

    // -------------------------------------------------------------------------
    // Public privacy policy page
    // -------------------------------------------------------------------------

    public function test_privacy_policy_page_is_accessible_without_authentication(): void
    {
        $this->get(route('privacy.index'))
            ->assertOk()
            ->assertSee('Privacy Policy');
    }

    // -------------------------------------------------------------------------
    // Authenticated GDPR rights page
    // -------------------------------------------------------------------------

    public function test_data_rights_page_requires_authentication(): void
    {
        $this->get(route('privacy.rights'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_data_rights_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('privacy.rights'))
            ->assertOk()
            ->assertSee('My Data Rights');
    }

    // -------------------------------------------------------------------------
    // Export flow
    // -------------------------------------------------------------------------

    public function test_export_request_requires_authentication(): void
    {
        $this->post(route('privacy.export'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_request_data_export(): void
    {
        $this->actingAs($this->user)
            ->post(route('privacy.export'))
            ->assertRedirect(route('privacy.rights'));

        $this->assertTrue(
            session()->has('gdpr_export_path'),
            'Session should contain the export file path after requesting an export.'
        );
    }

    public function test_data_export_file_is_valid_json(): void
    {
        $this->actingAs($this->user);
        $this->post(route('privacy.export'));

        $exportPath = session('gdpr_export_path');
        $this->assertNotNull($exportPath);

        $content = \Illuminate\Support\Facades\Storage::disk(
            config('data-protection.disk', 'local')
        )->get($exportPath);

        $this->assertNotNull($content);
        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('user', $decoded);
    }

    // -------------------------------------------------------------------------
    // Download: redirect when no session path
    // -------------------------------------------------------------------------

    public function test_download_redirects_when_no_export_in_session(): void
    {
        $this->actingAs($this->user)
            ->get(route('privacy.export.download'))
            ->assertRedirect(route('privacy.rights'));
    }
}
