<?php

namespace Tests\Feature;

use App\Models\BrandSetting;
use App\Models\Institution;
use App\Models\UiColorSetting;
use App\Models\User;
use App\Modules\Institutions\Queries\InstitutionQuery;
use App\Modules\User\UserQuery;
use App\Support\Cache\PageDataCache;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['page-cache.store' => 'array']);
        Cache::store('array')->flush();
        $this->seed(DatabaseSeeder::class);
    }

    private function user(): User
    {
        $institution = Institution::firstOrCreate(['code' => 'CACHE'], ['name' => 'Organization']);
        $user = User::create(['name' => 'Person', 'email' => uniqid().'@example.com',
            'password' => 'password-for-testing', 'institution_id' => $institution->id]);
        $user->assignRole('Cliente');

        return $user;
    }

    public function test_warm_lists_run_no_selects_and_exclude_passwords(): void
    {
        $user = $this->user();
        $query = app(UserQuery::class);
        $query->records($user->id, '', '', '');
        DB::enableQueryLog();
        DB::flushQueryLog();
        $items = $query->records($user->id, '', '', '');
        $this->assertCount(0, DB::getQueryLog());
        $this->assertArrayNotHasKey('password', $items->first()->getAttributes());
        $this->assertArrayNotHasKey('remember_token', $items->first()->getAttributes());
    }

    public function test_model_write_and_role_assignment_invalidate_lists(): void
    {
        $user = $this->user();
        $query = app(UserQuery::class);
        $query->records($user->id, '', '', '');
        $user->update(['name' => 'Changed']);
        $this->assertSame('Changed', $query->records($user->id, '', '', '')->first()->name);
        $user->assignRole('Técnico');
        $this->assertCount(2, $query->records($user->id, '', '', '')->first()->roles);
    }

    public function test_user_creation_updates_cached_institution_count(): void
    {
        $user = $this->user();
        $query = app(InstitutionQuery::class);
        $this->assertSame(1, $query->records($user->id, '', '')->first()->users_count);
        $this->user();
        $this->assertSame(2, $query->records($user->id, '', '')->first()->users_count);
    }

    public function test_fresh_entry_and_ttl_refresh_external_writes(): void
    {
        $user = $this->user();
        $query = app(UserQuery::class);
        $query->records($user->id, '', '', '');
        // Bulk SQL deliberately bypasses Eloquent observers.
        DB::table('users')->where('id', $user->id)->update(['name' => 'External']);
        app(PageDataCache::class)->freshEntry('users', $user->id);
        $this->assertSame('External', $query->records($user->id, '', '', '')->first()->name);
        DB::table('users')->where('id', $user->id)->update(['name' => 'Later']);
        $this->travel(31)->seconds();
        $this->assertSame('Later', $query->records($user->id, '', '', '')->first()->name);
    }

    public function test_brand_and_palette_refresh_after_edit(): void
    {
        BrandSetting::updateOrCreate(['id' => 1], ['name' => 'Before']);
        $this->assertSame('Before', BrandSetting::displayName());
        BrandSetting::find(1)->update(['name' => 'After']);
        $this->assertSame('After', BrandSetting::displayName());
        UiColorSetting::currentPalette();
        UiColorSetting::find(1)->update(['primary' => '#123456']);
        $this->assertSame('#123456', UiColorSetting::currentPalette()['primary']);
    }

    public function test_filters_and_user_scope_have_separate_keys(): void
    {
        $user = $this->user();
        $query = app(UserQuery::class);
        $this->assertCount(1, $query->records($user->id, '', '', ''));
        $this->assertCount(0, $query->records($user->id, 'No match', '', ''));
        $other = $this->user();
        $this->assertCount(2, $query->records($other->id, '', '', ''));
    }
}
