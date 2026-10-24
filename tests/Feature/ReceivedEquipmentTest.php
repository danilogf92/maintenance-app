<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\Equipment\Models\{Equipment,ReceivedEquipment};
use App\Modules\Equipment\Import\{EquipmentSource,ImportEquipment,LinkEquipmentTickets};
use App\Modules\Equipment\UI\EquipmentPage;
use App\Modules\DataReview\Queries\{ReviewStatus,EquipmentReview};
use App\Modules\Tickets\Models\{Ticket,NinoxTicketImport};
use App\Modules\Services\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan,DB,Storage};
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class ReceivedEquipmentTest extends TestCase
{
    use RefreshDatabase,WorkflowFixture;
    protected function setUp(): void { parent::setUp();$this->bootWorkflow();Storage::fake('local'); }
    private function loadReceived(): void
    {
        foreach (['data:load-services','data:load-catalogs','data:load-tickets','data:load-equipment'] as $command) {
            $code=Artisan::call($command);$this->assertSame(0,$code,Artisan::output());
        }
    }
    public function test_actual_equipment_import_links_only_reciprocal_tickets_without_inventing_organizations_or_plans(): void
    {
        $users=User::count();$before=[];
        foreach (['clients','institutions','maintenance_plans','maintenance_visits'] as $table) { $before[$table]=DB::table($table)->count(); }
        $this->loadReceived();
        $this->assertSame(204,Equipment::count());$this->assertSame(204,ReceivedEquipment::count());
        $this->assertSame(204,Equipment::whereNull('client_id')->whereNull('institution_id')->where('status','unknown')->count());
        $this->assertSame(204,Equipment::whereNotNull('brand_id')->count());$this->assertSame(200,Equipment::whereNotNull('equipment_model_id')->count());
        $this->assertSame(3,Equipment::where('serial','N/A')->count());$this->assertSame($users,User::count());
        foreach (['clients','institutions','maintenance_plans','maintenance_visits'] as $table) { $this->assertSame($before[$table],DB::table($table)->count()); }
        $this->assertSame(622,Ticket::whereNotNull('equipment_id')->count());$this->assertSame(3,Ticket::whereNull('equipment_id')->count());
        $this->assertNull(NinoxTicketImport::where('ninox_id','2030')->firstOrFail()->ticket->equipment_id);
        $this->assertSame(366,Service::whereNotNull('ticket_id')->count());
        $origin=ReceivedEquipment::where('source_id','158')->firstOrFail();
        $brand=DB::table('ninox_catalog_imports')->where('catalog','brands')->where('ninox_id','4')->value('record_id');
        $this->assertSame((int)$brand,$origin->equipment->brand_id);$this->assertSame('43S3221',$origin->equipment->serial);
        $this->assertStringNotContainsString('serial_number',$origin->getRawOriginal('payload'));$this->assertArrayNotHasKey('payload',$origin->toArray());
        $this->assertSame(48,DB::table('ninox_catalog_imports')->where('brand_basis','equipment_reference')->count());
    }
    public function test_repeat_import_keeps_all_persisted_values_versions_and_source_payloads(): void
    {
        $this->loadReceived();$before=[];
        foreach (['equipment','tickets','services','received_equipment','ninox_ticket_imports','ninox_service_imports','ninox_catalog_imports'] as $table) {
            $before[$table]=DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->assertSame(0,Artisan::call('data:load-equipment'));
        foreach ($before as $table=>$data) { $this->assertSame($data,DB::table($table)->orderBy('id')->get()->toJson(),$table); }
        $row=app(EquipmentSource::class)->read(base_path('database/imports/equipment/export/records.jsonl'))[0];
        $this->assertSame('conflicts',app(ImportEquipment::class)->execute([...$row,'serial_number'=>'CHANGED']));
        $this->assertSame('43S3221',ReceivedEquipment::where('source_id',$row['ninox_id'])->firstOrFail()->equipment->serial);
    }
    public function test_review_exposes_real_missing_model_history_tables_and_ambiguous_ticket(): void
    {
        $this->loadReceived();$review=app(EquipmentReview::class)->execute();
        $this->assertSame(204,$review['count']);$this->assertSame(622,$review['linkedTickets']);$this->assertSame(24,$review['pendingTickets']);
        $this->assertCount(6,$review['catalogEquipmentPending']);$this->assertCount(4,$review['missingModels'][106]);$this->assertSame([],$review['missingBrands']);
        $this->assertSame(['institutions'=>204,'clients'=>204],$review['histories']);
        $this->assertCount(1,$review['conflicts']);$this->assertSame('2030',$review['conflicts'][0]['reference']);
        $status=app(ReviewStatus::class)->execute();$this->assertSame(1,$status['missingEquipment']);$this->assertSame(2,$status['withoutEquipmentReference']);
        $this->get('/operations/data-review')->assertOk()->assertSee('Histórico de Centros Médicos Asociados')->assertSee('106')->assertSee('2030');
    }
    public function test_imported_equipment_is_readonly_and_normal_equipment_still_requires_real_organizations(): void
    {
        $this->loadReceived();$record=Equipment::firstOrFail();
        Livewire::test(EquipmentPage::class)->call('edit',$record->id)->assertSee('Detalles')->assertSee('Por verificar')->assertSee('Solo lectura');
        try { $this->saveWorkflow('equipment',['name'=>'Changed','status'=>'operational'],$record->id);$this->fail(); }
        catch (ValidationException $e) { $this->assertArrayHasKey('equipment_id',$e->errors()); }
        try { $this->saveWorkflow('equipment',['name'=>'Nuevo','status'=>'operational']);$this->fail(); }
        catch (ValidationException $e) { $this->assertArrayHasKey('client_id',$e->errors());$this->assertArrayHasKey('institution_id',$e->errors()); }
        $user=User::factory()->create();$this->actingAs($user);$this->get('/operations/equipment?record='.$record->id)->assertForbidden();
    }
    public function test_existing_ticket_equipment_is_not_reassigned_and_report_missing_relations_is_a_validation_error(): void
    {
        $this->loadReceived();$ticket=Ticket::whereNotNull('equipment_id')->firstOrFail();
        $other=Equipment::whereKeyNot($ticket->equipment_id)->firstOrFail();$ticket->update(['equipment_id'=>$other->id]);
        $result=app(LinkEquipmentTickets::class)->execute();$this->assertCount(2,$result['ticket_conflicts']);
        $this->assertSame($other->id,$ticket->fresh()->equipment_id);
        $service=Service::whereNotNull('ticket_id')->firstOrFail();
        $this->expectException(ValidationException::class);
        app(\App\Modules\TechnicalReports\Queries\ReportData::class)->execute($service,'TEST','Prueba');
    }
    public function test_dry_run_creates_nothing_and_rejects_invalid_source_before_partial_import(): void
    {
        $this->assertSame(0,Artisan::call('data:load-equipment',['--dry-run'=>true]));$this->assertSame(0,Equipment::count());
        $row=app(EquipmentSource::class)->read(base_path('database/imports/equipment/export/records.jsonl'))[0];
        $path=storage_path('framework/invalid-equipment.jsonl');
        file_put_contents($path,json_encode($row)."\n".json_encode([...$row,'ninox_id'=>'999999','manufactured_at'=>'invalid']));
        try { $this->assertSame(1,Artisan::call('data:load-equipment',['--source'=>$path]));$this->assertSame(0,Equipment::count()); }
        finally { unlink($path); }
    }
    public function test_upgrade_preserves_existing_operational_records_and_refuses_rollback_with_received_data(): void
    {
        $this->workflow();DB::statement('PRAGMA defer_foreign_keys = ON');
        $migration=require database_path('migrations/2026_10_19_000001_import_received_equipment.php');
        $migration->down();$before=DB::table('equipment')->orderBy('id')->get()->toJson();
        $migration->up();$this->assertSame($before,DB::table('equipment')->orderBy('id')->get()->toJson());
        $row=app(EquipmentSource::class)->read(base_path('database/imports/equipment/export/records.jsonl'))[0];
        $this->assertSame('created',app(ImportEquipment::class)->execute($row));
        $this->assertSame(2,Equipment::count());$this->assertSame(1,ReceivedEquipment::count());
        $this->assertSame([],DB::select('PRAGMA foreign_key_check'));
        $this->expectException(\LogicException::class);$migration->down();
    }

}
