<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\DataManagement\Actions\StartEquipmentHistoryImport;
use App\Modules\DataManagement\Import\CatalogRecord;
use App\Modules\DataManagement\Jobs\ImportEquipmentHistoryPackage;
use App\Modules\DataManagement\Models\{DataSourceRecord, EquipmentHistory, DataImportRun};
use App\Modules\DataManagement\Support\{HistoryValue, MappingStore};
use App\Modules\DataManagement\UI\DataManagementPage;
use App\Modules\Equipment\Models\{Equipment, ReceivedEquipment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Queue, Storage};
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class EquipmentHistoryImportTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    private Equipment $equipment;
    private int $clientId, $institutionId;
    protected function setUp(): void
    {
        parent::setUp(); $this->bootWorkflow(); Storage::fake('local');
        $path = Storage::disk('local')->path('mapping.json'); $mappings = json_decode(file_get_contents(base_path('data/correspondencias.json.example')), true);
        unset($mappings['Centro Asociado'], $mappings['Clientes Asociados']); file_put_contents($path, json_encode($mappings));
        config(['data-management.mapping_path' => $path, 'data-management.inventory_root' => Storage::disk('local')->path('empty')]);
        $f = $this->workflow(); $this->equipment = $f['equipment']; $this->equipment->update(['client_id' => null, 'institution_id' => null]);
        $this->parent('clients', '700'); $this->parent('institutions', '900');
        $this->clientId = (int)DataSourceRecord::where('entity','clients')->where('source_id','700')->value('record_id');
        $this->institutionId = (int)DataSourceRecord::where('entity','institutions')->where('source_id','900')->value('record_id');
        ReceivedEquipment::create(['source_table'=>'C','source_id'=>'158','equipment_id'=>$this->equipment->id,'source_hash'=>str_repeat('a',64),
            'payload'=>['ninox_id'=>'158','ninox_table'=>'C','customer_history'=>[205], 'equipment_medical_centers_history'=>[135]]]);
    }
    private function parent(string $entity, string $id): int
    {
        app(CatalogRecord::class)->execute($entity, ['ninox_table'=>$entity==='clients'?'A':'B', 'ninox_id'=>$id,
            $entity==='clients'?'client_name_or_business_name':'name'=>'Fuente '.$entity.' '.$id]);
        return (int)DataSourceRecord::where('entity',$entity)->where('source_id',$id)->value('record_id');
    }
    private function qc(array $changes=[]): array { return $changes + ['ninox_table'=>'QC','ninox_id'=>'135','equipment'=>158,'centers'=>900,'assigned_date'=>'2024-02-01','notes'=>'Original reservado']; }
    private function rc(array $changes=[]): array { return $changes + ['ninox_table'=>'RC','ninox_id'=>'205','equipments'=>158,'clients'=>700,'assignation_datr'=>'2024-02-01']; }
    private function reverse(string $field, array $ids): void
    {
        $source = ReceivedEquipment::first(); $payload = $source->payload; $payload[$field] = $ids; $source->update(['payload'=>$payload]);
    }
    private function package(?array $qc=null, ?array $rc=null): array
    {
        $id = str_replace('-','',(string)\Illuminate\Support\Str::uuid()); $directory='data-management/history-inbox/'.$id;
        $manifest=['version'=>1,'id'=>$id,'created_at'=>now()->utc()->format('Y-m-d\TH:i:s.u\Z'),'tables'=>[]];
        foreach (['equipment_institution_histories'=>$qc??[$this->qc()], 'equipment_client_histories'=>$rc??[$this->rc()]] as $entity=>$rows) {
            $isCenter=$entity==='equipment_institution_histories'; $content=implode("\n",array_map(fn($r)=>json_encode($r),$rows))."\n";
            Storage::disk('local')->put($directory.'/'.$entity.'.jsonl',$content);
            $manifest['tables'][$entity]=['source_table'=>$isCenter?'QC':'RC','source_name'=>$isCenter?'Centro_Asociado':'Clientes_Asociados',
                'records'=>count($rows),'sha256'=>hash('sha256',$content)];
        }
        Storage::disk('local')->put($directory.'/package.json',json_encode($manifest)); return $manifest;
    }
    private function start(): DataImportRun { Queue::fake(); return app(StartEquipmentHistoryImport::class)->execute(); }
    private function execute(DataImportRun $run): DataImportRun { app()->call([new ImportEquipmentHistoryPackage($run->id),'handle']);return $run->fresh(); }
    public function test_queue_preserves_histories_links_local_ids_and_keeps_original_payload_private(): void
    {
        $this->package(); $version=$this->equipment->lock_version; $users=User::count(); $run=$this->start();
        Queue::assertPushed(ImportEquipmentHistoryPackage::class,fn($j)=>$j->connection==='database' && $j->queue==='data-imports');
        $this->assertNull($this->equipment->fresh()->client_id); $run=$this->execute($run);
        $this->assertSame('completed',$run->status); $this->assertSame($this->clientId,$this->equipment->fresh()->client_id);
        $this->assertSame($this->institutionId,$this->equipment->fresh()->institution_id); $this->assertSame($version+2,$this->equipment->fresh()->lock_version);
        $this->assertDatabaseCount('equipment_client_histories',1); $this->assertDatabaseCount('equipment_institution_histories',1); $this->assertSame($users,User::count());
        $history=EquipmentHistory::forEntity('equipment_institution_histories')->first(); $this->assertSame('Original reservado',$history->payload['notes']);
        $this->assertStringNotContainsString('Original reservado',$history->getRawOriginal('payload')); $this->assertArrayNotHasKey('payload',$history->toArray());
        $ack=json_decode(Storage::disk('local')->get('data-management/imports/'.$run->id.'/ack.json'),true);
        $this->assertSame(2,$ack['created_records']);$this->assertSame(2,$ack['linked_relations']);$this->assertFalse($ack['requires_relationship_validation']);
        $this->assertSame('equipment_institution_histories',app(MappingStore::class)->resolve('Centro Asociado'));
        Livewire::test(DataManagementPage::class)->call('selectTab','imports')->assertSee('Vínculos de equipos')->call('downloadImport',$run->id)->assertFileDownloaded();
    }
    public function test_repetition_is_idempotent_and_changed_source_does_not_overwrite_history_or_existing_links(): void
    {
        $this->package();$this->execute($this->start()); $before=$this->equipment->fresh()->toArray();
        $again=$this->execute($this->start()); $this->assertSame(1,$again->summary['equipment_client_histories']['existing']);$this->assertSame($before,$this->equipment->fresh()->toArray());
        $this->package([$this->qc(['assigned_date'=>'2025-01-01'])]);$changed=$this->execute($this->start());
        $this->assertSame(1,$changed->summary['equipment_institution_histories']['conflict']);$this->assertSame($before,$this->equipment->fresh()->toArray());
        $this->assertDatabaseHas('equipment_institution_histories',['source_id'=>'135','assigned_at'=>'2024-02-01 00:00:00']);
    }
    public function test_latest_assignment_is_selected_by_date_and_not_largest_source_id(): void
    {
        $other=$this->parent('institutions','901');$this->reverse('equipment_medical_centers_history',[135,136]);
        $this->package([$this->qc(['assigned_date'=>'2025-01-01','centers'=>901]),$this->qc(['ninox_id'=>'136','assigned_date'=>'2023-01-01'])]);
        $this->execute($this->start());$this->assertSame($other,$this->equipment->fresh()->institution_id);$this->assertDatabaseCount('equipment_institution_histories',2);
    }
    public function test_same_day_tie_and_missing_dates_with_different_targets_remain_pending(): void
    {
        $this->parent('institutions','901');$this->reverse('equipment_medical_centers_history',[135,136]);
        $this->package([$this->qc(),$this->qc(['ninox_id'=>'136','centers'=>901])]);$run=$this->execute($this->start());
        $this->assertNull($this->equipment->fresh()->institution_id);$this->assertContains('assignment_not_determined',array_column($run->issues,'code'));
        $this->assertNotNull($this->equipment->fresh()->client_id);
    }
    public function test_missing_date_is_safe_only_when_all_destinations_coincide_and_future_assignments_are_not_applied(): void
    {
        $this->package([$this->qc(['assigned_date'=>null])],[$this->rc(['assignation_datr'=>'2100-01-01'])]);$run=$this->execute($this->start());
        $this->assertSame($this->institutionId,$this->equipment->fresh()->institution_id);$this->assertNull($this->equipment->fresh()->client_id);
        $this->assertContains('date_missing',array_column($run->issues,'code'));
    }
    public function test_unknown_date_formats_and_multi_value_parent_references_are_preserved_without_links(): void
    {
        $this->package([$this->qc(['assigned_date'=>'02/03/2024'])],[$this->rc(['clients'=>[700,701]])]);$run=$this->execute($this->start());
        $this->assertNull($this->equipment->fresh()->institution_id);$this->assertNull($this->equipment->fresh()->client_id);
        $this->assertContains('date_invalid',array_column($run->issues,'code'));$this->assertContains('parent_reference_pending',array_column($run->issues,'code'));
        $this->assertDatabaseCount('equipment_institution_histories',1);
    }
    public function test_missing_parent_can_be_resolved_on_a_repeat_without_duplicating_the_history(): void
    {
        $this->package([$this->qc(['centers'=>999])]);$this->execute($this->start());$this->assertNull($this->equipment->fresh()->institution_id);
        $parent=$this->parent('institutions','999');$run=$this->execute($this->start());$this->assertSame($parent,$this->equipment->fresh()->institution_id);
        $this->assertSame(1,$run->summary['equipment_institution_histories']['updated']);$this->assertDatabaseCount('equipment_institution_histories',1);
    }
    public function test_missing_reverse_rows_and_extra_claims_block_automatic_selection(): void
    {
        $this->reverse('equipment_medical_centers_history',[135,136]);$this->package();$run=$this->execute($this->start());
        $this->assertNull($this->equipment->fresh()->institution_id);$this->assertContains('history_references_incomplete',array_column($run->issues,'code'));
    }
    public function test_existing_different_local_link_is_preserved_and_reported(): void
    {
        $other=$this->parent('clients','701');$this->equipment->update(['client_id'=>$other]);$this->package();$run=$this->execute($this->start());
        $this->assertSame($other,$this->equipment->fresh()->client_id);$this->assertContains('existing_link_conflict',array_column($run->issues,'code'));
    }
    public function test_snapshot_tampering_and_permission_revocation_prevent_business_writes(): void
    {
        $this->package();$run=$this->start();Storage::disk('local')->put('data-management/imports/'.$run->id.'/equipment_client_histories.jsonl','tampered');
        $this->assertThrows(fn()=>$this->execute($run),\Illuminate\Validation\ValidationException::class);$this->assertSame('failed',$run->fresh()->status);
        $this->assertDatabaseCount('equipment_institution_histories',0);$this->assertNull($this->equipment->fresh()->institution_id);
        $run=$this->start();$this->admin->update(['is_active'=>false]);$this->execute($run);$this->assertSame('failed',$run->fresh()->status);
    }
    public function test_failure_rolls_back_histories_and_links_and_writes_failure_ack(): void
    {
        $this->package();$run=$this->start();$resolver=\Mockery::mock(\App\Modules\DataManagement\Actions\ResolveEquipmentAssignments::class);
        $resolver->shouldReceive('execute')->andThrow(new \RuntimeException('PRIVATE_ERROR'));app()->instance(get_class(new \App\Modules\DataManagement\Actions\ResolveEquipmentAssignments),$resolver);
        $this->assertThrows(fn()=>$this->execute($run),\RuntimeException::class);$this->assertDatabaseCount('equipment_client_histories',0);
        $this->assertNull($this->equipment->fresh()->client_id);$this->assertSame('failed',$run->fresh()->status);
        $ack=Storage::disk('local')->get('data-management/imports/'.$run->id.'/ack.json');$this->assertFalse(json_decode($ack,true)['imported']);$this->assertStringNotContainsString('PRIVATE_ERROR',json_encode($run->fresh()->issues));
    }
    public function test_real_database_queue_works_without_a_browser_session_and_view_only_users_cannot_start(): void
    {
        $this->package();$run=app(StartEquipmentHistoryImport::class)->execute();auth()->logout();Queue::connection('database')->pop('data-imports')->fire();
        $this->assertSame('completed',$run->fresh()->status);$this->assertNotNull($this->equipment->fresh()->client_id);
        $viewer=User::factory()->create(['can_access_admin'=>true]);$viewer->givePermissionTo(['panel.access','data_management.view']);$this->actingAs($viewer);
        Livewire::test(DataManagementPage::class)->call('startHistoryImport')->assertForbidden();
    }
    public function test_date_parser_checks_real_calendar_dates_and_normalizes_offsets(): void
    {
        $this->assertSame('date_invalid',HistoryValue::date('2024-02-31')['issue']);$this->assertSame('date_invalid',HistoryValue::date(45000)['issue']);
        $this->assertSame('2024-01-02 00:30:00',HistoryValue::date('2024-01-02T05:30:00Z')['value']);
        $this->assertNull(HistoryValue::references([['id'=>1]]));$this->assertSame('7',HistoryValue::single([7]));
    }
    public function test_different_destinations_with_one_undated_history_remain_pending(): void
    {
        $this->parent('institutions','901');$this->reverse('equipment_medical_centers_history',[135,136]);
        $this->package([$this->qc(),$this->qc(['ninox_id'=>'136','centers'=>901,'assigned_date'=>null])]);$this->execute($this->start());
        $this->assertNull($this->equipment->fresh()->institution_id);
    }
    public function test_unlisted_claim_does_not_choose_a_destination(): void
    {
        $this->parent('institutions','901');$this->package([$this->qc(),$this->qc(['ninox_id'=>'136','centers'=>901])]);$run=$this->execute($this->start());
        $this->assertNull($this->equipment->fresh()->institution_id);$this->assertContains('reverse_reference_mismatch',array_column($run->issues,'code'));
    }
    public function test_existing_correspondence_is_never_overwritten_by_history_setup(): void
    {
        $this->package();$store=app(MappingStore::class);$data=$store->read();$data['Centro Asociado']='maintenance_contracts';file_put_contents($store->path(),json_encode($data));
        $this->assertThrows(fn()=>$this->start(),\Illuminate\Validation\ValidationException::class);$this->assertSame('maintenance_contracts',$store->resolve('Centro Asociado'));
        $this->assertDatabaseCount('data_import_runs',0);
    }
    public function test_warm_equipment_cache_is_refreshed_and_history_is_visible_in_details(): void
    {
        $query=app(\App\Modules\Equipment\Queries\EquipmentQuery::class);$this->assertNull($query->records('','')->first()->client_id);
        $this->package();$this->execute($this->start());$this->assertSame($this->clientId,$query->records('','')->first()->client_id);
        $this->get('/operations/equipment?record='.$this->equipment->id)->assertOk()->assertSee('Historial de clientes de equipos')->assertSee('Destino identificado');
    }
    public function test_mixed_day_and_timestamp_precision_with_different_destinations_is_ambiguous(): void
    {
        $this->parent('institutions','901');$this->reverse('equipment_medical_centers_history',[135,136]);
        $this->package([$this->qc(),$this->qc(['ninox_id'=>'136','centers'=>901,'assigned_date'=>'2024-02-01T15:00:00Z'])]);$run=$this->execute($this->start());
        $this->assertNull($this->equipment->fresh()->institution_id);$this->assertContains('assignment_not_determined',array_column($run->issues,'code'));
    }
    public function test_precise_timestamps_on_the_same_day_choose_the_later_instant(): void
    {
        $other=$this->parent('institutions','901');$this->reverse('equipment_medical_centers_history',[135,136]);
        $this->package([$this->qc(['assigned_date'=>'2024-02-01T14:00:00Z']),$this->qc(['ninox_id'=>'136','centers'=>901,'assigned_date'=>'2024-02-01T15:00:00Z'])]);$this->execute($this->start());
        $this->assertSame($other,$this->equipment->fresh()->institution_id);
    }
}
