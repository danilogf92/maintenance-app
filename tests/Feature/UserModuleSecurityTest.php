<?php

namespace Tests\Feature;

use App\Livewire\User\UserPage;
use App\Models\Institution;
use App\Models\OperationView;
use App\Models\User;
use App\Modules\User\DeleteUser;
use App\Modules\User\UserManager;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserModuleSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function person(string $role = 'Cliente'): User
    {
        $institution = Institution::firstOrCreate(['code' => 'USER-TEST'], ['name' => 'Institución']);
        $user = User::create(['name' => 'Persona', 'email' => uniqid().'@example.com',
            'password' => 'password-for-testing', 'institution_id' => $institution->id]);
        $user->assignRole($role);

        return $user;
    }

    private function data(User $user, array $extra = []): array
    {
        return array_replace(['name' => $user->name, 'email' => $user->email,
            'institution_id' => $user->institution_id, 'is_active' => true,
            'can_access_admin' => false, 'role_ids' => $user->roles->pluck('id')->all()], $extra);
    }

    public function test_module_routes_render_and_clients_cannot_enter(): void
    {
        $this->actingAs($this->person('SuperAdmin'))->get('/operations/users')->assertOk();
        $this->actingAs($this->person())->get('/operations/users')->assertForbidden();
    }

    public function test_admin_cannot_edit_privileged_account_with_panel_disabled(): void
    {
        $actor = $this->person('Administrador');
        $target = $this->person('Administrador');
        $this->assertFalse($target->can_access_admin);
        $this->expectException(HttpException::class);
        app(UserManager::class)->save($actor, $this->data($target, ['password' => 'attacker-password', 'role_ids' => [Role::findByName('Cliente')->id]]), $target);
    }

    public function test_direct_privileges_cannot_be_taken_over_by_a_user_manager(): void
    {
        $actor = $this->person();
        $actor->givePermissionTo('users.manage');
        $target = $this->person();
        $target->givePermissionTo('institutions.manage');
        $this->expectException(HttpException::class);
        app(UserManager::class)->save($actor, $this->data($target, ['password' => 'attacker-password']), $target);
    }

    public function test_role_from_another_guard_is_rejected(): void
    {
        $actor = $this->person('SuperAdmin');
        $target = $this->person();
        $role = Role::create(['name' => 'External', 'guard_name' => 'api']);
        $this->expectException(ValidationException::class);
        app(UserManager::class)->save($actor, $this->data($target, ['role_ids' => [$role->id]]), $target);
    }

    public function test_editor_saves_dirty_record_before_closing(): void
    {
        $actor = $this->person('SuperAdmin');
        $target = $this->person();
        Livewire::actingAs($actor)->test(UserPage::class)->call('edit', $target->id)
            ->set('name', 'Cambio pendiente')->call('closeForm')->assertSet('formOpen', false);
        $this->assertSame('Cambio pendiente', $target->fresh()->name);
    }

    public function test_invalid_record_cannot_close_or_switch_tab(): void
    {
        $actor = $this->person('SuperAdmin');
        $target = $this->person();
        $editor = Livewire::actingAs($actor)->test(UserPage::class)->call('edit', $target->id);
        $firstView = $editor->get('currentViewId');
        $otherView = OperationView::where('user_id', $actor->id)->where('module', 'users')->where('id', '!=', $firstView)->first()->id;
        $editor->set('email', 'invalid')->call('selectView', $otherView)
            ->assertHasErrors('email')->assertSet('formOpen', true)->assertSet('currentViewId', $firstView);
        $this->assertNotSame('invalid', $target->fresh()->email);
    }

    public function test_deletion_requires_confirmation_and_cancel_clears_it(): void
    {
        $actor = $this->person('SuperAdmin');
        $target = $this->person();
        $editor = Livewire::actingAs($actor)->test(UserPage::class)->call('edit', $target->id);
        $editor->call('deleteConfirmedRecord');
        $this->assertModelExists($target);
        $editor->call('requestOpenRecordDelete')->call('cancelDelete')->call('deleteConfirmedRecord');
        $this->assertModelExists($target);
        $editor->call('requestOpenRecordDelete')->assertSet('recordDeletionId', $target->id)
            ->call('deleteConfirmedRecord')->assertSet('formOpen', false);
        $this->assertModelMissing($target);
    }

    public function test_administrative_account_cannot_be_deleted(): void
    {
        $actor = $this->person('SuperAdmin');
        $target = $this->person('Administrador');
        $this->expectException(ValidationException::class);
        app(DeleteUser::class)->execute($actor, $target->id);
    }

    public function test_permission_is_rechecked_on_each_livewire_request(): void
    {
        $actor = $this->person('Administrador');
        $editor = Livewire::actingAs($actor)->test(UserPage::class);
        $actor->syncRoles(['Cliente']);
        $editor->call('create')->assertForbidden();
    }
}
