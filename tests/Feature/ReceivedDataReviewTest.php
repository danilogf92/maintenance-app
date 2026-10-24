<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\DataReview\Actions\RemoveDemonstrationData;
use App\Modules\DataReview\Queries\ReviewStatus;
use App\Modules\Services\Models\Service;
use App\Modules\Tickets\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan,DB,Storage};
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class ReceivedDataReviewTest extends TestCase
{
    use RefreshDatabase,WorkflowFixture;
    protected function setUp(): void { parent::setUp();$this->bootWorkflow();Storage::fake('local'); }
    public function test_cleaning_removes_the_known_demo_graph_but_preserves_received_records_and_admin(): void
    {
        $this->seed(\Database\Seeders\WorkflowDemoSeeder::class);
        $this->seed(\Database\Seeders\ContactDemoSeeder::class);
        $this->seed(\Database\Seeders\PlanningDemoSeeder::class);
        $this->assertSame(0,Artisan::call('data:load-services'));$this->assertSame(0,Artisan::call('data:load-catalogs'));$this->assertSame(0,Artisan::call('data:load-tickets'));
        $received=DB::table('ninox_service_imports')->orderBy('id')->get()->toJson();
        $result=app(RemoveDemonstrationData::class)->execute();
        $this->assertGreaterThan(0,array_sum($result['removed']));$this->assertSame(369,Service::count());$this->assertSame(625,Ticket::count());
        $this->assertSame(366,Service::whereNotNull('ticket_id')->count());$this->assertSame(0,DB::table('equipment')->count());
        $this->assertSame(0,DB::table('maintenance_plans')->count());$this->assertSame(0,DB::table('contacts')->count());
        $this->assertSame(41,DB::table('brands')->count());$this->assertSame(53,DB::table('equipment_models')->count());
        $this->assertTrue(User::whereKey($this->admin->id)->exists());$this->assertSame($received,DB::table('ninox_service_imports')->orderBy('id')->get()->toJson());
        $again=app(RemoveDemonstrationData::class)->execute();$this->assertSame(0,array_sum($again['removed']));
    }
    public function test_synthetic_accounts_are_removed_but_real_users_and_superadmins_are_preserved(): void
    {
        $this->seed(\Database\Seeders\DemoDataSeeder::class);$this->assertGreaterThan(1,User::count());
        $real=User::factory()->create();app(RemoveDemonstrationData::class)->execute();
        $this->assertTrue(User::whereKey($real->id)->exists());$this->assertTrue(User::whereKey($this->admin->id)->exists());
        $this->assertSame(0,User::where('email','like','demo.%@example.test')->count());
    }
    public function test_unknown_records_and_real_dependencies_block_demo_deletion(): void
    {
        $this->seed(\Database\Seeders\WorkflowDemoSeeder::class);$demo=Service::where('code','DEMO-SV-1')->firstOrFail();
        $report=DB::table('technical_reports')->where('service_id',$demo->id)->first();
        DB::table('technical_reports')->where('id',$report->id)->update(['code'=>'REAL-REPORT']);
        $result=app(RemoveDemonstrationData::class)->execute();
        $this->assertTrue(Service::whereKey($demo->id)->exists());$this->assertArrayHasKey('services',$result['protected']);
    }
    public function test_review_is_restricted_and_shows_next_files_from_current_counts(): void
    {
        $this->get('/operations/data-review')->assertOk()->assertSee('Qué subir a continuación')->assertSee('Equipos');
        $user=User::factory()->create();$user->assignRole('Técnico');$this->actingAs($user);
        $this->get('/operations/data-review')->assertForbidden();
    }
    public function test_screen_labels_and_preview_use_received_data_without_fabricated_rows(): void
    {
        $this->assertSame(0,Artisan::call('data:load-services'));
        $service=Service::firstOrFail();$response=$this->get('/operations/services?record='.$service->id)->assertOk()->assertSee('Detalles');
        $response->assertDontSee('Origen Ninox')->assertDontSee('>Ninox<',false);
        $provider=app(\App\Modules\ReportTemplates\Queries\TemplateSampleData::class);
        $small=$provider->execute(4);$large=$provider->execute(80);
        $this->assertSame($service->code,$small['report']['number']);$this->assertSame($small,$large);
        $this->assertSame([],$large['activities']);$this->assertSame([],$large['parts']);$this->assertSame([],$large['recommendations']);
    }
}
