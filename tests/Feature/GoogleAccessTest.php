<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Access\Models\GoogleAccount;
use App\Modules\Access\Queries\GoogleProviderFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Mockery;
use Tests\TestCase;

class GoogleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google' => ['enabled' => true, 'client_id' => 'test-client',
            'client_secret' => 'test-secret', 'redirect' => 'http://localhost/auth/google/callback']]);
    }

    private function begin(?User $user = null): string
    {
        $response = $user ? $this->actingAs($user)->post('/profile/google/connect') : $this->get('/auth/google');
        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertNotEmpty($query['state']);
        $this->assertSame('select_account', $query['prompt']);
        return $query['state'];
    }

    private function identity(string $email, string $subject = 'google-123', bool $verified = true): MockHandler
    {
        $handler = new MockHandler([new Response(200, [], json_encode(['access_token' => 'temporary-token', 'expires_in' => 3600])),
            new Response(200, [], json_encode(['sub' => $subject, 'email' => $email, 'email_verified' => $verified]))]);
        $factory = Mockery::mock(GoogleProviderFactory::class);
        $factory->shouldReceive('make')->andReturnUsing(fn () => Socialite::buildProvider(GoogleProvider::class, config('services.google'))
            ->enablePKCE()->setHttpClient(new Client(['handler' => HandlerStack::create($handler)])));
        $this->app->instance(GoogleProviderFactory::class, $factory);
        return $handler;
    }

    private function finishGoogle(string $state)
    {
        return $this->get('/auth/google/callback?'.http_build_query(['state' => $state, 'code' => 'single-use-code']));
    }

    public function test_unconfigured_button_and_route_leave_password_login_available(): void
    {
        config(['services.google.client_secret' => null]);
        $this->get('/login')->assertOk()->assertSee('Continuar con Google')->assertSee('name="password"', false);
        $this->get('/auth/google')->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->assertGuest();
    }

    public function test_explicit_link_then_google_login_preserves_password_and_permissions(): void
    {
        $user = User::factory()->create(); $password = $user->password;
        $state = $this->begin($user); $this->identity($user->email);
        $this->finishGoogle($state)->assertRedirect('/profile')->assertSessionHas('status');
        $this->assertDatabaseHas('google_accounts', ['user_id' => $user->id, 'subject' => 'google-123']);
        $this->assertSame($password, $user->fresh()->password);
        $this->post('/logout'); $state = $this->begin(); $this->identity($user->email);
        $this->finishGoogle($state)->assertRedirect('/dashboard'); $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->fresh()->can_access_admin);
        $this->assertSame(0, $user->roles()->count());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_matching_email_alone_does_not_link_or_create_an_account(): void
    {
        $user = User::factory()->create(); $state = $this->begin(); $this->identity($user->email);
        $this->finishGoogle($state)->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->assertGuest(); $this->assertDatabaseCount('google_accounts', 0); $this->assertDatabaseCount('users', 1);
    }

    public function test_unknown_google_user_cannot_register(): void
    {
        $state = $this->begin(); $this->identity('stranger@example.com');
        $this->finishGoogle($state)->assertSessionHasErrors('google');
        $this->assertGuest(); $this->assertDatabaseCount('users', 0);
    }

    public function test_unverified_email_and_wrong_email_cannot_be_linked(): void
    {
        $user = User::factory()->create(); $state = $this->begin($user); $this->identity($user->email, verified: false);
        $this->finishGoogle($state)->assertSessionHasErrors('google');
        $state = $this->begin($user); $this->identity('different@example.com');
        $this->finishGoogle($state)->assertSessionHasErrors('google'); $this->assertDatabaseCount('google_accounts', 0);
    }

    public function test_missing_mismatched_expired_and_replayed_state_are_rejected(): void
    {
        $this->finishGoogle('missing')->assertSessionHasErrors('google');
        $this->begin(); $this->finishGoogle('mismatched')->assertSessionHasErrors('google');
        $state = $this->begin(); $this->travel(11)->minutes(); $this->finishGoogle($state)->assertSessionHasErrors('google');
        $this->travelBack();
        $user = User::factory()->create(); $state = $this->begin($user); $handler = $this->identity($user->email);
        $this->finishGoogle($state)->assertSessionHas('status'); $this->assertSame(0, $handler->count());
        $this->finishGoogle($state)->assertSessionHasErrors('google');
    }

    public function test_cancelled_consent_clears_flow_without_requesting_tokens(): void
    {
        $state = $this->begin();
        $this->get('/auth/google/callback?'.http_build_query(['state' => $state, 'error' => 'access_denied']))
            ->assertRedirect('/login')->assertSessionHasErrors('google')->assertSessionMissing('google_oauth_context');
        $this->assertGuest();
    }

    public function test_disabled_account_or_changed_email_cannot_login_with_existing_link(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        GoogleAccount::create(['user_id' => $user->id, 'subject' => 'google-123', 'email' => $user->email]);
        $state = $this->begin(); $this->identity($user->email); $this->finishGoogle($state)->assertSessionHasErrors('google');
        $this->assertGuest(); $user->update(['is_active' => true, 'email' => 'new@example.com']);
        $state = $this->begin(); $this->identity('old@example.com'); $this->finishGoogle($state)->assertSessionHasErrors('google');
        $this->assertGuest();
    }

    public function test_disconnect_only_removes_own_link_and_password_still_works(): void
    {
        $a = User::factory()->create(['password' => 'known-password']); $b = User::factory()->create();
        foreach ([$a, $b] as $user) GoogleAccount::create(['user_id' => $user->id, 'subject' => 'sub-'.$user->id, 'email' => $user->email]);
        $this->actingAs($a)->delete('/profile/google', ['user_id' => $b->id])->assertRedirect('/profile');
        $this->assertDatabaseMissing('google_accounts', ['user_id' => $a->id]);
        $this->assertDatabaseHas('google_accounts', ['user_id' => $b->id]);
        $this->post('/logout'); $this->post('/login', ['email' => $a->email, 'password' => 'known-password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($a);
    }

    public function test_signed_out_linking_and_network_errors_are_safe(): void
    {
        $this->post('/profile/google/connect')->assertRedirect('/login');
        $this->delete('/profile/google')->assertRedirect('/login');
        $user = User::factory()->create(); $state = $this->begin($user); $this->post('/logout');
        $this->finishGoogle($state)->assertSessionHasErrors('google'); $this->assertDatabaseCount('google_accounts', 0);
        $state = $this->begin();
        $factory = Mockery::mock(GoogleProviderFactory::class);
        $factory->shouldReceive('make')->andThrow(new \RuntimeException('private-provider-secret'));
        $this->app->instance(GoogleProviderFactory::class, $factory);
        $this->finishGoogle($state)->assertRedirect('/login')->assertSessionHasErrors('google');
        $this->get('/login')->assertDontSee('private-provider-secret');
    }
    public function test_missing_pkce_and_already_claimed_identity_cannot_be_used(): void
    {
        $state = $this->begin(); $this->withSession(['code_verifier' => null]);
        $this->finishGoogle($state)->assertSessionHasErrors('google');
        $a = User::factory()->create(); $b = User::factory()->create();
        GoogleAccount::create(['user_id' => $b->id, 'subject' => 'claimed', 'email' => $b->email]);
        $state = $this->begin($a); $this->identity($a->email, 'claimed');
        $this->finishGoogle($state)->assertSessionHasErrors('google');
        $this->assertDatabaseMissing('google_accounts', ['user_id' => $a->id]);
        $this->assertDatabaseHas('google_accounts', ['user_id' => $b->id, 'subject' => 'claimed']);
    }

}
