<?php

namespace Tests\Feature;

use App\Livewire\Institution\InstitutionPage;
use App\Models\Institution;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecordNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_navigation_saves_pending_changes_and_keeps_editor_open(): void
    {
        $this->seed(DatabaseSeeder::class);
        $first = Institution::create(['name' => 'Primera', 'code' => 'NAV-FIRST', 'type' => 'other']);
        $last = Institution::create(['name' => 'Última', 'code' => 'NAV-LAST', 'type' => 'other']);
        $actor = User::create(['name' => 'Administrador', 'email' => 'navigation@example.test',
            'password' => 'password-for-testing', 'institution_id' => $first->id]);
        $actor->assignRole('SuperAdmin');
        $component = Livewire::actingAs($actor)->test(InstitutionPage::class)
            ->call('edit', $first->id)->set('name', 'Nombre actualizado')
            ->call('edit', $last->id)->assertSet('editingId', $last->id)
            ->assertSet('formOpen', true)->assertSet('name', 'Última');
        $this->assertDatabaseHas('institutions', ['id' => $first->id, 'name' => 'Nombre actualizado']);
        $component->set('name', '')->call('edit', $first->id)
            ->assertHasErrors(['name'])->assertSet('editingId', $last->id)
            ->assertSet('formOpen', true);
        $this->assertDatabaseHas('institutions', ['id' => $last->id, 'name' => 'Última']);
    }
}
