<?php

namespace Tests\Browser;

use App\Models\Document;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EditorBrowserTest extends DuskTestCase
{
    public function test_authenticated_user_can_open_editor_page(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::query()->create([
            'team_id' => $user->ownedTeams()->firstOrFail()->id,
            'user_id' => $user->id,
            'title' => 'Editor Browser Test',
            'content' => '<p>Initial content</p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                ->visit('/documents/'.$document->id)
                ->assertSee('Editor')
                ->assertSee('Document Title')
                ->assertInputValue('#title', 'Editor Browser Test');
        });
    }

    public function test_user_can_update_document_title_from_editor(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::query()->create([
            'team_id' => $user->ownedTeams()->firstOrFail()->id,
            'user_id' => $user->id,
            'title' => 'Original Browser Title',
            'content' => '<p>Initial content</p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                ->visit('/documents/'.$document->id)
                ->type('#title', 'Updated Browser Title')
                ->click('button[wire\\:click="save"]')
                ->pause(1000)
                ->assertInputValue('#title', 'Updated Browser Title');
        });

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Browser Title',
        ]);
    }
}
