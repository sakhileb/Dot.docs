<?php

namespace Tests\Feature;

use App\Livewire\Documents\FormBuilder;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormBuilderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_form_builder_page(): void
    {
        $user = \App\Models\User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Form Doc',
            'content' => '<p>Welcome</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->actingAs($user)
            ->get(route('documents.form-builder', $document))
            ->assertOk()
            ->assertSee('Form Builder')
            ->assertSee('Build Form Fields');
    }

    public function test_user_can_add_form_field(): void
    {
        $user = \App\Models\User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Builder Doc',
            'content' => '<p>Collect details</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        Livewire::actingAs($user)
            ->test(FormBuilder::class, ['document' => $document])
            ->set('label', 'Customer Name')
            ->set('fieldType', 'text')
            ->set('placeholder', 'Jane Doe')
            ->set('isRequired', true)
            ->call('addField')
            ->assertHasNoErrors()
            ->assertSee('Customer Name');

        $this->assertDatabaseHas('document_form_fields', [
            'document_id' => $document->id,
            'label' => 'Customer Name',
            'name' => 'customer_name',
            'field_type' => 'text',
            'is_required' => true,
        ]);
    }

    public function test_user_can_sync_generated_form_into_document(): void
    {
        $user = \App\Models\User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Proposal Intake',
            'content' => '<p>Start of document</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        Livewire::actingAs($user)
            ->test(FormBuilder::class, ['document' => $document])
            ->set('label', 'Contact Email')
            ->set('fieldType', 'email')
            ->set('placeholder', 'team@example.com')
            ->call('addField')
            ->call('syncToDocument')
            ->assertHasNoErrors();

        $document->refresh();

        $this->assertStringContainsString('data-document-form-builder="true"', (string) $document->content);
        $this->assertStringContainsString('Contact Email', (string) $document->content);
        $this->assertStringContainsString('<form', (string) $document->content);
    }
}