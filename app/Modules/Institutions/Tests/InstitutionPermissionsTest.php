<?php

namespace App\Modules\Institutions\Tests;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\Actions\SaveInstitution;
use App\Modules\Institutions\UI\InstitutionPage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class InstitutionPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_action_requires_institution_permission(): void
    {
        $this->seed(DatabaseSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('Cliente');
        $count = Institution::count();
        try {
            app(SaveInstitution::class)->execute($actor, ['name' => 'Forbidden', 'code' => 'X', 'type' => 'clinic']);
            $this->fail('Missing permission must reject the action.');
        } catch (AuthorizationException) {
            $this->assertSame($count, Institution::count());
        }
    }

    public function test_visible_columns_cannot_be_forged_in_a_livewire_request(): void
    {
        $this->seed(DatabaseSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('SuperAdmin');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::actingAs($actor)->test(InstitutionPage::class)->set('visibleColumns', ['password']);
    }
}
