<?php

namespace Tests\Feature;

use App\Livewire\User\UserPage;
use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\UI\InstitutionPage;
use App\Modules\Shared\RecordCreation;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AutomaticCreationTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $this->seed(DatabaseSeeder::class);
        $institution = Institution::create(['name' => 'Base', 'code' => 'AUTO-BASE']);
        $user = User::create(['name' => 'Actor', 'email' => 'auto-actor@example.test',
            'password' => 'password-for-testing', 'institution_id' => $institution->id]);
        $user->assignRole('SuperAdmin');

        return $user;
    }

    public function test_user_is_created_automatically_and_following_changes_update_it(): void
    {
        $actor = $this->actor();
        $editor = Livewire::actingAs($actor)->test(UserPage::class)->set('search', 'test')->call('create')
            ->assertSet('search', '')->set('name', 'Persona')->set('email', 'automatic@example.test')
            ->set('institution_id', (string) $actor->institution_id)->set('password', 'Initial-password-123');
        $session = $editor->get('editorSession');
        $editor->call('autosaveDraft', $session)->assertReturned('saved')->assertSet('formOpen', true)
            ->assertSet('password', '');
        $id = $editor->get('editingId');
        $this->assertNotNull($id);
        $editor->call('autosaveDraft', $session)->assertReturned('saved');
        $this->assertSame(1, User::where('email', 'automatic@example.test')->count());
        $editor->set('job_title', 'Ingeniero')->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertDatabaseHas('users', ['id' => $id, 'job_title' => 'Ingeniero']);
    }

    public function test_incomplete_draft_and_wrong_session_do_not_create_records(): void
    {
        $actor = $this->actor();
        $count = Institution::count();
        $editor = Livewire::actingAs($actor)->test(InstitutionPage::class)->call('create')->set('name', 'Nueva');
        $session = $editor->get('editorSession');
        $editor->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors(['code']);
        $this->assertSame($count, Institution::count());
        $this->assertDatabaseCount('operation_record_creations', 0);
        $editor->set('code', 'AUTO-NEW')->call('autosaveDraft', 'wrong')->assertReturned('stale');
        $editor->call('autosaveDraft', $session)->assertReturned('saved')->assertSet('formOpen', true);
        $editor->call('closeForm')->call('autosaveDraft', $session)->assertReturned('stale');
        $this->assertSame($count + 1, Institution::count());
    }

    public function test_retried_creation_uses_same_database_record(): void
    {
        $actor = $this->actor();
        $creator = app(RecordCreation::class);
        $token = (string) Str::uuid();
        $first = $creator->remember($actor->id, 'institutions', $token, Institution::class,
            fn () => Institution::create(['name' => 'Única', 'code' => 'UNIQUE-DRAFT']));
        $second = $creator->remember($actor->id, 'institutions', $token, Institution::class,
            fn () => throw new \RuntimeException('Must not insert twice'));
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('operation_record_creations', 1);
    }

    public function test_empty_new_record_can_close_without_creating_a_row(): void
    {
        $actor = $this->actor();
        $count = Institution::count();
        $editor = Livewire::actingAs($actor)->test(InstitutionPage::class)->call('create');
        $session = $editor->get('editorSession');
        $editor->call('closeForm')->assertSet('formOpen', false)->assertSet('editorSession', '')
            ->call('autosaveDraft', $session)->assertReturned('stale');
        $this->assertSame($count, Institution::count());
        $this->assertDatabaseCount('operation_record_creations', 0);
    }

    public function test_incomplete_new_record_can_close_after_validation_errors(): void
    {
        $actor = $this->actor();
        $count = Institution::count();
        $editor = Livewire::actingAs($actor)->test(InstitutionPage::class)->call('create')->set('name', 'Pendiente');
        $session = $editor->get('editorSession');
        $editor->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors('code');
        $editor->call('closeForm')->assertSet('formOpen', false)->assertSet('recordDirty', false)
            ->assertSet('editorSession', '')->assertHasNoErrors()
            ->call('autosaveDraft', $session)->assertReturned('stale');
        $this->assertSame($count, Institution::count());
        $this->assertDatabaseCount('operation_record_creations', 0);
        $editor->call('create')->assertSet('name', '')->assertSet('formOpen', true);
        $this->assertNotSame($session, $editor->get('editorSession'));
    }

    public function test_quick_navigation_flushes_a_valid_new_draft(): void
    {
        $actor = $this->actor();
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('create')
            ->set('name', 'Antes de salir')->set('code', 'QUICK-DRAFT')->call('closeForm')
            ->assertSet('formOpen', false);
        $this->assertDatabaseHas('institutions', ['code' => 'QUICK-DRAFT']);
    }
}
