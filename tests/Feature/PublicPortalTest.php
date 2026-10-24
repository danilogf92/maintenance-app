<?php

namespace Tests\Feature;

use App\Models\BrandSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_read_presentation_without_access_to_operational_records(): void
    {
        $this->get('/')->assertOk()->assertViewIs('public.welcome')
            ->assertSee(config('portal.title'))->assertSee('Vista conceptual')
            ->assertSee(route('login'))->assertDontSee('/operations/tickets');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/operations/tickets')->assertRedirect('/login');
    }

    public function test_login_keeps_csrf_credentials_and_errors_without_reflecting_password(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="_token"', false)
            ->assertSee('autocomplete="username"', false)->assertSee('autocomplete="current-password"', false);
        $this->from('/login')->post('/login', ['email' => 'missing@example.com', 'password' => 'private-password'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->get('/login')->assertSee('Credenciales incorrectas o cuenta desactivada.')
            ->assertDontSee('private-password')->assertSee('role="alert"', false);
    }

    public function test_public_brand_uses_settings_and_escapes_user_supplied_text(): void
    {
        BrandSetting::create(['id' => 1, 'name' => '<script>alert(1)</script>', 'logo_path' => 'brand/company.png']);
        $this->get('/')->assertOk()->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('/storage/brand/company.png');
    }

    public function test_signed_in_user_can_view_presentation_and_return_to_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user)->get('/')->assertOk()->assertSee('Ir a mi panel')
            ->assertSee(route('dashboard'))->assertDontSee($user->email);
        $this->get('/login')->assertRedirect('/dashboard');
    }
}
