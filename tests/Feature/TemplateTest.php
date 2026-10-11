<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\BrandSetting;
use App\Models\Institution;
use App\Models\User;
use App\Modules\User\UserManager;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function person(string $role = 'Cliente', bool $panel = false): User
    {
        $org = Institution::firstOrCreate(['code' => 'TEST'], ['name' => 'Institución prueba']);
        $u = User::create(['name' => 'Persona', 'email' => uniqid().'@example.com', 'password' => 'test-password-123', 'institution_id' => $org->id, 'can_access_admin' => $panel]);
        $u->assignRole($role);

        return $u;
    }

    private function data(User $user, array $extra = []): array
    {
        return array_replace(['name' => $user->name, 'email' => $user->email, 'institution_id' => $user->institution_id, 'is_active' => true, 'can_access_admin' => false, 'role_ids' => $user->roles()->pluck('id')->all()], $extra);
    }

    public function test_guest_has_no_dashboard_or_public_registration(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/register')->assertNotFound();
    }

    public function test_login_and_logout(): void
    {
        $u = $this->person();
        $this->post('/login', ['email' => $u->email, 'password' => 'test-password-123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($u);
        $this->get('/dashboard')->assertOk()->assertSee('Institución prueba');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_disabled_account_cannot_login(): void
    {
        $u = $this->person();
        $u->update(['is_active' => false]);
        $this->post('/login', ['email' => $u->email, 'password' => 'test-password-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_disabled_existing_session_is_revoked(): void
    {
        $u = $this->person();
        $this->actingAs($u);
        $u->update(['is_active' => false]);
        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_access_requires_flag_and_permission(): void
    {
        $u = $this->person('Cliente', true);
        $this->assertFalse($u->canAccessPanel(Filament::getPanel('admin')));
        $u->assignRole('Administrador');
        $this->assertTrue($u->canAccessPanel(Filament::getPanel('admin')));
        $u->update(['can_access_admin' => false]);
        $this->assertFalse($u->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_normal_user_cannot_open_users_admin(): void
    {
        $this->actingAs($this->person())->get('/admin/users')->assertForbidden();
    }

    public function test_superadmin_can_render_resources(): void
    {
        $u = $this->person('SuperAdmin', true);
        $this->actingAs($u);
        BrandSetting::create(['id' => 1, 'name' => 'Marca prueba']);
        foreach (['/admin', '/admin/users', '/admin/users/create', '/admin/users/'.$u->id.'/edit', '/admin/institutions', '/admin/roles', '/admin/brand-settings/1/edit'] as $route) {
            $this->get($route)->assertOk();
        }
    }

    public function test_admin_can_create_participant_and_password_is_hashed(): void
    {
        $actor = $this->person('Administrador', true);
        $input = $this->data($actor, ['email' => 'new@example.com', 'password' => 'new-password-123', 'role_ids' => [Role::findByName('Cliente')->id]]);
        $user = app(UserManager::class)->save($actor, $input);
        $this->assertTrue($user->hasRole('Cliente'));
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }

    public function test_admin_cannot_assign_superadmin(): void
    {
        $actor = $this->person('Administrador', true);
        $target = $this->person();
        $this->expectException(HttpException::class);
        app(UserManager::class)->save($actor, $this->data($target, ['role_ids' => [Role::findByName('SuperAdmin')->id]]), $target);
    }

    public function test_admin_cannot_enable_panel_for_participant(): void
    {
        $actor = $this->person('Administrador', true);
        $target = $this->person();
        $this->expectException(HttpException::class);
        app(UserManager::class)->save($actor, $this->data($target, ['can_access_admin' => true]), $target);
    }

    public function test_self_deactivation_is_rejected(): void
    {
        $actor = $this->person('SuperAdmin', true);
        $this->expectException(ValidationException::class);
        app(UserManager::class)->save($actor, $this->data($actor, ['is_active' => false]), $actor);
    }

    public function test_invalid_institution_does_not_modify_user(): void
    {
        $actor = $this->person('SuperAdmin', true);
        $target = $this->person();
        try {
            app(UserManager::class)->save($actor, $this->data($target, ['name' => 'Modified', 'institution_id' => 999]), $target);
            $this->fail('Expected validation');
        } catch (ValidationException) {
        }
        $this->assertSame('Persona', $target->fresh()->name);
    }

    public function test_edit_preserves_password_and_multiple_roles(): void
    {
        $actor = $this->person('SuperAdmin', true);
        $target = $this->person();
        $hash = $target->password;
        $ids = Role::whereIn('name', ['Cliente', 'Técnico'])->pluck('id')->all();
        app(UserManager::class)->save($actor, $this->data($target, ['password' => '', 'role_ids' => $ids]), $target);
        $this->assertSame($hash, $target->fresh()->password);
        $this->assertCount(2, $target->fresh()->roles);
    }

    public function test_filament_create_and_edit_use_manager(): void
    {
        $actor = $this->person('SuperAdmin', true);
        $this->actingAs($actor);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $input = $this->data($actor, ['name' => 'Form user', 'email' => 'form@example.com', 'password' => 'form-password-123', 'role_ids' => [Role::findByName('Cliente')->id]]);
        Livewire::test(CreateUser::class)->fillForm($input)->call('create')->assertHasNoFormErrors();
        $target = User::where('email', 'form@example.com')->firstOrFail();
        $this->assertTrue($target->hasRole('Cliente'));
        $hash = $target->password;
        Livewire::test(EditUser::class, ['record' => $target->id])->fillForm(['name' => 'Edited', 'password' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Edited', $target->fresh()->name);
        $this->assertSame($hash, $target->fresh()->password);
    }

    public function test_role_editor_is_superadmin_only_and_reserved_role_protected(): void
    {
        $this->actingAs($this->person('Administrador', true))->get('/admin/roles')->assertForbidden();
    }

    public function test_reserved_superadmin_role_cannot_be_edited(): void
    {
        $this->actingAs($this->person('SuperAdmin', true))->get('/admin/roles/'.Role::findByName('SuperAdmin')->id.'/edit')->assertForbidden();
    }

    public function test_branding_applies_to_login_and_home(): void
    {
        BrandSetting::create(['id' => 1, 'name' => 'Academia personalizada']);
        $this->get('/login')->assertOk()->assertSee('Academia personalizada');
        $this->actingAs($this->person())->get('/dashboard')->assertOk()->assertSee('Academia personalizada');
    }

    public function test_setup_creates_superadmin_and_cannot_overwrite(): void
    {
        $this->artisan('template:setup', ['email' => 'owner@example.com'])->expectsQuestion('Nombre del superadministrador', 'Danilo')->expectsQuestion('Nombre de su institución', 'Corpomédica')->expectsQuestion('Nombre de la plataforma', 'Academia')->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'setup-password-123')->expectsQuestion('Repita la contraseña', 'setup-password-123')->assertExitCode(0);
        $this->assertTrue(User::where('email', 'owner@example.com')->first()->hasRole('SuperAdmin'));
        $this->artisan('template:setup', ['email' => 'other@example.com'])->assertExitCode(1);
    }
}
