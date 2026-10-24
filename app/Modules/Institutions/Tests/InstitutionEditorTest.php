<?php

namespace App\Modules\Institutions\Tests;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\Configuration\Fields;
use App\Modules\Institutions\Configuration\Tabs;
use App\Modules\Institutions\InstitutionModule;
use App\Modules\Institutions\UI\InstitutionPage;
use App\Modules\Institutions\UI\RelatedUserEditor;
use App\Modules\Institutions\UI\RelatedUsers;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstitutionEditorTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $this->seed(DatabaseSeeder::class);
        $institution = Institution::create(['name' => 'Institución piloto', 'code' => 'PILOT', 'type' => 'company']);
        $actor = User::create(['name' => 'Actor', 'email' => 'pilot@example.test',
            'password' => 'test-password-123', 'institution_id' => $institution->id]);
        $actor->assignRole('SuperAdmin');

        return $actor;
    }

    public function test_module_code_respects_file_and_function_limits(): void
    {
        $this->assertSame([], CodeLimits::violations(dirname(__DIR__)));
    }

    public function test_fields_can_move_tabs_without_changing_definitions(): void
    {
        $tabs = Tabs::definitions();
        $tabs['general']['sections'][0]['fields'][] = 'contact_email';
        $tabs['contact']['sections'][0]['fields'] = ['contact_phone'];
        $editor = InstitutionModule::editor($tabs);
        $this->assertContains('contact_email', $editor['editorTabs']['general']['sections'][0]['fields']);
        $this->assertSame(Fields::definitions(), $editor['editorFields']);
        $this->assertStringContainsString('--record-editor-gap:16px', $editor['editorLayoutStyle']);
    }

    public function test_duplicate_fields_fail_with_an_explicit_configuration_error(): void
    {
        $tabs = Tabs::definitions();
        $tabs['contact']['sections'][0]['fields'][] = 'name';
        $this->expectException(\LogicException::class);
        InstitutionModule::editor($tabs);
    }

    public function test_relation_is_lazy_and_form_data_remains_when_opening_it(): void
    {
        $actor = $this->actor();
        $editor = Livewire::actingAs($actor)->test(InstitutionPage::class)->call('edit', $actor->institution_id)
            ->assertSet('loadedEditorRelations', [])->assertDontSee('Buscar usuarios relacionados…');
        $session = $editor->get('editorSession');
        $editor->set('contact_phone', '0991234567')->call('loadEditorRelation', 'users')
            ->assertSet('loadedEditorRelations', ['users'])->assertSet('editorSession', $session)
            ->assertSet('formOpen', true)->assertSee('Buscar usuarios relacionados…');
        $this->assertDatabaseHas('institutions', ['id' => $actor->institution_id, 'contact_phone' => '0991234567']);
        $editor->call('create')->assertSet('loadedEditorRelations', []);
    }

    public function test_incomplete_draft_does_not_load_relations_or_insert_a_row(): void
    {
        $actor = $this->actor();
        $count = Institution::count();
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('create')->set('contact_email', 'new@example.test')
            ->call('loadEditorRelation', 'users')->assertSet('loadedEditorRelations', [])
            ->assertHasErrors('name')->assertSet('editingId', null)->call('closeForm')->assertSet('formOpen', false);
        $this->assertSame($count, Institution::count());
    }

    public function test_related_editor_reuses_user_autosave_and_preserves_password(): void
    {
        $actor = $this->actor();
        $hash = $actor->password;
        $editor = Livewire::actingAs($actor)->test(RelatedUserEditor::class,
            ['institutionId' => $actor->institution_id, 'recordId' => $actor->id]);
        $session = $editor->get('editorSession');
        $editor->set('job_title', 'Ingeniero')->call('autosaveRecord', $actor->id, $session)->assertReturned('saved')
            ->assertSet('formOpen', true)->call('closeForm')->assertSet('formOpen', false)
            ->assertDispatched('institution-related-user-closed');
        $this->assertSame('Ingeniero', $actor->fresh()->job_title);
        $this->assertSame($hash, $actor->fresh()->password);
    }

    public function test_related_list_mounts_the_reusable_editor_and_can_reopen_it(): void
    {
        $actor = $this->actor();
        $list = Livewire::actingAs($actor)->test(RelatedUsers::class, ['institutionId' => $actor->institution_id])
            ->call('openUser', $actor->id)->assertSet('relatedUserId', $actor->id)->assertSee('Editar usuario');
        $session = $list->get('childSession');
        $list->call('closeUser')->assertSet('relatedUserId', null)->call('openUser', $actor->id);
        $this->assertNotSame($session, $list->get('childSession'));
    }

    public function test_related_users_cannot_open_a_user_from_another_institution(): void
    {
        $actor = $this->actor();
        $other = Institution::create(['name' => 'Otra', 'code' => 'OTHER']);
        $foreign = User::create(['name' => 'Ajeno', 'email' => 'foreign@example.test',
            'password' => 'test-password-123', 'institution_id' => $other->id]);
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($actor)->test(RelatedUsers::class, ['institutionId' => $actor->institution_id])
            ->call('openUser', $foreign->id);
    }

    public function test_institution_permission_does_not_grant_access_to_users(): void
    {
        $actor = $this->actor();
        $actor->syncRoles([]);
        $actor->givePermissionTo('institutions.manage');
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('edit', $actor->institution_id)
            ->call('loadEditorRelation', 'users')->assertStatus(403);
        Livewire::test(RelatedUsers::class, ['institutionId' => $actor->institution_id])->assertStatus(403);
    }
}
