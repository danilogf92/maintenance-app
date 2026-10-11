<?php

namespace Tests\Feature;

use App\Models\BrandSetting;
use App\Models\UiColorSetting;
use App\Modules\Institutions\Queries\InstitutionQuery;
use App\Modules\User\UserQuery;
use App\Support\Cache\PageDataCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PagePerformanceTest extends TestCase
{
    use RefreshDatabase;

    private ?string $cachePath = null;

    protected function tearDown(): void
    {
        if ($this->cachePath) {
            File::deleteDirectory($this->cachePath);
        }
        parent::tearDown();
    }

    public function test_500_record_data_snapshot_has_zero_warm_database_queries(): void
    {
        $this->cachePath = sys_get_temp_dir().'/maintenance-page-cache-'.uniqid();
        config(['page-cache.store' => 'benchmark-pages', 'cache.stores.benchmark-pages' => [
            'driver' => 'file', 'path' => $this->cachePath, 'lock_path' => $this->cachePath,
        ]]);
        $now = now();
        $institutions = $users = [];
        for ($id = 1; $id <= 500; $id++) {
            $institutions[] = ['id' => $id, 'name' => 'Institution '.$id, 'code' => 'BENCH-'.$id,
                'type' => 'company', 'created_at' => $now, 'updated_at' => $now];
            $users[] = ['id' => $id, 'name' => 'Person '.$id, 'email' => $id.'@example.com',
                'password' => 'not-used-for-login', 'institution_id' => $id,
                'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('institutions')->insert($institutions);
        DB::table('users')->insert($users);
        BrandSetting::create(['name' => 'Benchmark']);
        $snapshot = function () {
            return [app(UserQuery::class)->records(1, '', '', ''),
                app(InstitutionQuery::class)->records(1, '', ''),
                app(InstitutionQuery::class)->options(), BrandSetting::current(), UiColorSetting::currentPalette()];
        };
        app(PageDataCache::class)->freshEntry('users', 1);
        app(PageDataCache::class)->freshEntry('institutions', 1);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $start = hrtime(true);
        $cold = $snapshot();
        $coldMs = (hrtime(true) - $start) / 1e6;
        $coldQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        $start = hrtime(true);
        $warm = $snapshot();
        $warmMs = (hrtime(true) - $start) / 1e6;
        $warmQueries = count(DB::getQueryLog());
        $this->assertCount(500, $cold[0]);
        $this->assertCount(500, $cold[1]);
        $this->assertCount(500, $warm[0]);
        $this->assertSame(0, $warmQueries);
        $this->assertGreaterThan(0, $coldQueries);
        fwrite(STDOUT, sprintf("\n500+500 snapshot: cold=%d queries %.2fms; warm=%d queries %.2fms\n",
            $coldQueries, $coldMs, $warmQueries, $warmMs));
    }
}
