<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use App\Support\Demo\ResetDemoData;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DemoRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_preserves_superadmin_credentials_and_creates_varied_records(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');
        $password = $admin->password;
        $old = User::factory()->create();
        $result = app(ResetDemoData::class)->execute(500);
        $this->assertSame(1, $result['preserved_admins']);
        $this->assertModelMissing($old);
        $this->assertSame($password, $admin->fresh()->password);
        $this->assertSame(501, User::count());
        $this->assertSame(501, Institution::count());
        $this->assertSame(9, Institution::where('code', 'like', 'DEMO-%')->distinct()->count('city'));
        $this->assertSame(5, Institution::where('code', 'like', 'DEMO-%')->distinct()->count('type'));
        $this->assertSame(6, User::where('id', '!=', $admin->id)->distinct()->count('job_title'));
        $this->assertSame(0, User::where('id', '!=', $admin->id)->where('can_access_admin', true)->count());
    }

    public function test_no_superadmin_means_no_deletions(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create();
        try {
            app(ResetDemoData::class)->execute(2);
            $this->fail('A SuperAdmin must exist.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
        $this->assertModelExists($user);
    }

    public function test_command_rejects_production_before_touching_records(): void
    {
        $user = User::factory()->create();
        $this->app->detectEnvironment(fn () => 'production');
        try {
            app(ResetDemoData::class)->execute(2);
            $this->fail('Production must reject the reset.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertModelExists($user);
    }
}
