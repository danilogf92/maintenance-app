<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\DataManagement\Actions\{StartCatalogImport, CatalogImportDiagnostics};
use App\Modules\DataManagement\Jobs\ImportCatalogPackage;
use App\Modules\DataManagement\Models\{DataImportRun, DataSourceRecord};
use App\Modules\DataManagement\Support\{CatalogPackage, MappingStore};
use App\Modules\DataManagement\UI\DataManagementPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Queue, Storage};
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class DataCatalogImportTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void
    {
        parent::setUp(); $this->bootWorkflow(); Storage::fake('local');
        $path = Storage::disk('local')->path('mapping.json'); copy(base_path('data/correspondencias.json.example'), $path);
        config(['data-management.mapping_path' => $path, 'data-management.inventory_root' => Storage::disk('local')->path('empty')]);
    }
    private function package(array $clients = [], array $institutions = []): array
    {
        $id = str_replace('-', '', (string)\Illuminate\Support\Str::uuid()); $directory = 'data-management/inbox/'.$id;
        $manifest = ['version' => 1, 'id' => $id, 'created_at' => now()->utc()->format('Y-m-d\TH:i:s.u\Z'), 'tables' => []];
        foreach (['clients' => $clients ?: [['ninox_table' => 'A', 'ninox_id' => '7', 'client_name_or_business_name' => 'Cliente de fuente',
            'billing_email' => 'compras@example.test', 'ruc_or_identification' => '0012345678001', 'address' => 'Dirección original']],
            'institutions' => $institutions ?: [['ninox_table' => 'B', 'ninox_id' => '11', 'name' => 'Centro de fuente', 'address' => 'Dirección centro', 'city_id' => 8, 'contact_id' => 91]]] as $entity => $records) {
            $json = implode("\n", array_map(fn ($r) => json_encode($r), $records))."\n";
            Storage::disk('local')->put($directory.'/'.$entity.'.jsonl', $json);
            $manifest['tables'][$entity] = ['source_table' => $entity === 'clients' ? 'A' : 'B', 'source_name' => $entity === 'clients' ? 'Clientes' : 'Centros Medicos',
                'records' => count($records), 'sha256' => hash('sha256', $json)];
        }
        Storage::disk('local')->put($directory.'/package.json', json_encode($manifest));
        return $manifest + ['directory' => $directory];
    }
    private function start(): DataImportRun { Queue::fake(); return app(StartCatalogImport::class)->execute(); }
    private function execute(DataImportRun $run): DataImportRun
    {
        app()->call([new ImportCatalogPackage($run->id), 'handle']); return $run->fresh();
    }
    public function test_import_is_queued_and_worker_imports_only_real_values_with_encrypted_source_and_no_equipment_guessing(): void
    {
        $workflow = $this->workflow(); $workflow['equipment']->update(['client_id' => null, 'institution_id' => null]);
        $users = User::count(); $institutions = DB::table('institutions')->count(); $clients = DB::table('clients')->count();
        $this->package(); $run = $this->start();
        Queue::assertPushed(ImportCatalogPackage::class, fn ($job) => $job->connection === 'database' && $job->queue === 'data-imports');
        $this->assertSame('queued', $run->status); $this->assertDatabaseCount('clients', $clients);
        $run = $this->execute($run);
        $this->assertSame('completed_with_observations', $run->status); $this->assertSame(1, $run->summary['clients']['created']);
        Storage::disk('local')->assertExists('data-management/imports/'.$run->id.'/ack.json');
        $this->assertDatabaseCount('clients', $clients + 1); $this->assertDatabaseCount('institutions', $institutions + 1); $this->assertDatabaseCount('users', $users);
        $this->assertDatabaseHas('clients', ['name' => 'Cliente de fuente', 'tax_id' => '0012345678001']);
        $this->assertDatabaseHas('institutions', ['name' => 'Centro de fuente', 'address' => 'Dirección centro', 'type' => null, 'city' => null]);
        $source = DataSourceRecord::where('entity', 'institutions')->firstOrFail(); $this->assertSame(8, $source->payload['city_id']);
        $this->assertStringNotContainsString('Centro de fuente', DB::table('data_source_records')->where('id', $source->id)->value('payload'));
        $this->assertNull($workflow['equipment']->fresh()->client_id); $this->assertNull($workflow['equipment']->fresh()->institution_id);
    }
    public function test_repeat_keeps_local_edits_and_source_changes_become_conflicts(): void
    {
        $package = $this->package(); $first = $this->execute($this->start());
        $client = DataSourceRecord::where('entity', 'clients')->first(); DB::table('clients')->where('id', $client->record_id)->update(['name' => 'Edición local']);
        $second = $this->execute($this->start()); $this->assertSame(1, $second->summary['clients']['existing']);
        $this->assertDatabaseHas('clients', ['name' => 'Edición local']); $this->assertDatabaseCount('data_source_records', 2);
        $directory = $package['directory']; $manifest = json_decode(Storage::disk('local')->get($directory.'/package.json'), true);
        $json = Storage::disk('local')->get($directory.'/clients.jsonl'); $json = str_replace('Cliente de fuente', 'Cambio en origen', $json);
        $manifest['tables']['clients']['sha256'] = hash('sha256', $json);
        Storage::disk('local')->put($directory.'/clients.jsonl', $json); Storage::disk('local')->put($directory.'/package.json', json_encode($manifest));
        $third = $this->execute($this->start()); $this->assertSame(1, $third->summary['clients']['conflict']);
        $this->assertDatabaseHas('clients', ['name' => 'Edición local']); $this->assertDatabaseCount('clients', 1);
    }
    public function test_bad_optional_values_are_reported_and_preserved_while_missing_names_are_rejected(): void
    {
        $this->package([['ninox_table' => 'A', 'ninox_id' => '1', 'client_name_or_business_name' => 'Cliente válido', 'billing_email' => 'mal correo', 'phone' => ['ambiguous']],
            ['ninox_table' => 'A', 'ninox_id' => '2', 'client_name_or_business_name' => '']]);
        $run = $this->execute($this->start()); $this->assertSame(1, $run->summary['clients']['created']); $this->assertSame(1, $run->summary['clients']['rejected']);
        $this->assertDatabaseHas('clients', ['name' => 'Cliente válido', 'email' => null, 'phone' => null]);
        $this->assertSame('mal correo', DataSourceRecord::where('entity', 'clients')->first()->payload['billing_email']);
        $this->assertContains('field_pending', array_column($run->issues, 'code')); $this->assertContains('name_invalid', array_column($run->issues, 'code'));
    }
    public function test_duplicates_wrong_table_tampering_and_invalid_json_are_rejected_before_business_writes(): void
    {
        $row = ['ninox_table' => 'A', 'ninox_id' => '1', 'client_name_or_business_name' => 'Duplicado'];
        $package = $this->package([$row, $row]); $reader = app(CatalogPackage::class);
        $this->assertThrows(fn () => $reader->rows($reader->read($package['directory'])), ValidationException::class);
        $package = $this->package([['ninox_table' => 'B', 'ninox_id' => '1']]);
        $this->assertThrows(fn () => $reader->rows($reader->read($package['directory'])), ValidationException::class);
        Storage::disk('local')->put($package['directory'].'/clients.jsonl', 'CHANGED');
        $this->assertThrows(fn () => $reader->read($package['directory']), ValidationException::class);
        $this->assertDatabaseCount('clients', 0); $this->assertDatabaseCount('data_source_records', 0);
    }
    public function test_active_run_and_wrong_mapping_prevent_queueing_another_import(): void
    {
        $this->package(); $run = $this->start();
        $this->assertThrows(fn () => app(StartCatalogImport::class)->execute(), ValidationException::class);
        $run->update(['status' => 'failed', 'finished_at' => now()]);
        $store = app(MappingStore::class); $data = $store->read(); $data['Clientes'] = 'equipment'; file_put_contents($store->path(), json_encode($data));
        $this->assertThrows(fn () => app(StartCatalogImport::class)->execute(), ValidationException::class);
        Queue::assertPushed(ImportCatalogPackage::class, 1); $this->assertDatabaseCount('clients', 0);
    }
    public function test_prepared_input_is_frozen_and_worker_revalidates_hashes(): void
    {
        $package = $this->package(); $run = $this->start();
        Storage::disk('local')->put($package['directory'].'/clients.jsonl', 'CHANGED');
        $this->execute($run); $this->assertDatabaseCount('clients', 1);
        $this->package(); $run = $this->start(); Storage::disk('local')->put('data-management/imports/'.$run->id.'/institutions.jsonl', 'CHANGED');
        $this->assertThrows(fn () => $this->execute($run), ValidationException::class);
        $this->assertSame('failed', $run->fresh()->status); $this->assertDatabaseCount('data_source_records', 2);
    }
    public function test_view_only_user_cannot_start_and_revoked_actor_is_rejected_by_worker(): void
    {
        $this->package(); $run = $this->start();
        $viewer = User::factory()->create(['can_access_admin' => true]); $viewer->givePermissionTo(['panel.access', 'data_management.view']); $this->actingAs($viewer);
        Livewire::test(DataManagementPage::class)->call('selectTab', 'imports')->assertSee('Clientes y centros médicos');
        Livewire::test(DataManagementPage::class)->call('startCatalogImport')->assertForbidden();
        $this->admin->update(['is_active' => false]); $this->execute($run);
        $this->assertSame('failed', $run->fresh()->status); $this->assertDatabaseCount('clients', 0);
    }
    public function test_actual_database_queue_runs_without_browser_authentication_and_ack_matches_counts(): void
    {
        $this->package(); $run = app(StartCatalogImport::class)->execute();
        $this->assertDatabaseHas('jobs', ['queue' => 'data-imports']); $this->assertDatabaseCount('clients', 0); auth()->logout();
        Queue::connection('database')->pop('data-imports')->fire(); $run->refresh();
        $this->assertSame(1, $run->summary['clients']['created']); $path = app(CatalogImportDiagnostics::class)->create($run);
        $zip = new \ZipArchive; $zip->open($path); $ack = json_decode($zip->getFromName('ack.json'), true);
        $this->assertTrue($ack['imported']); $this->assertSame(2, $ack['created_records']); $this->assertNotFalse($zip->getFromName('pending-links.json'));
        $this->assertStringNotContainsString('compras@example.test', $zip->getFromName('errors.json')); $zip->close();
    }
    public function test_ui_renders_counts_and_downloads_real_result(): void
    {
        $this->package(); $run = $this->execute($this->start());
        Livewire::test(DataManagementPage::class)->call('selectTab', 'imports')->assertSee('Centros Medicos')->assertSee('Completado con observaciones')
            ->assertSee('Ya existentes')->call('downloadImport', $run->id)->assertFileDownloaded();
    }
    public function test_unexpected_failure_rolls_back_business_rows_and_retains_a_downloadable_failure(): void
    {
        $this->package(); $run = $this->start();
        $importer = \Mockery::mock(\App\Modules\DataManagement\Import\CatalogRecord::class)->makePartial();
        $importer->shouldReceive('execute')->with('clients', \Mockery::type('array'))->once()->passthru();
        $importer->shouldReceive('execute')->with('institutions', \Mockery::type('array'))->once()->andThrow(new \RuntimeException('PRIVATE_SOURCE_VALUE'));
        app()->instance(\App\Modules\DataManagement\Import\CatalogRecord::class, $importer);
        $this->assertThrows(fn () => $this->execute($run), \RuntimeException::class);
        $this->assertDatabaseCount('clients', 0); $this->assertDatabaseCount('data_source_records', 0); $this->assertSame('failed', $run->fresh()->status);
        $this->assertStringNotContainsString('PRIVATE_SOURCE_VALUE', json_encode($run->fresh()->issues));
        Livewire::test(DataManagementPage::class)->call('downloadImport', $run->id)->assertFileDownloaded();
    }
    public function test_new_clients_and_institutions_are_visible_immediately_after_import_with_warm_caches(): void
    {
        $clients = app(\App\Modules\Clients\Queries\ClientQuery::class);
        $institutions = app(\App\Modules\Institutions\Queries\InstitutionQuery::class);
        $this->assertCount(0, $clients->records('', '')); $before = $institutions->options()->count();
        $this->package(); $this->execute($this->start());
        $this->assertCount(1, $clients->records('', '')); $this->assertCount($before + 1, $institutions->options());
    }
    public function test_future_demo_cleanup_protects_received_clients_and_centers_even_when_codes_are_edited(): void
    {
        $this->package(); $this->execute($this->start());
        foreach (DataSourceRecord::all() as $source) { DB::table($source->entity)->where('id', $source->record_id)->update(['code' => 'DEMO-LOCAL-EDIT']); }
        app(\App\Modules\DataReview\Actions\RemoveDemonstrationData::class)->execute();
        $this->assertDatabaseHas('clients', ['name' => 'Cliente de fuente']); $this->assertDatabaseHas('institutions', ['name' => 'Centro de fuente']);
        $review = app(\App\Modules\DataReview\Queries\ReviewStatus::class)->execute();
        $this->assertSame(1, $review['received']['Clientes']); $this->assertSame(1, $review['received']['Centros médicos']);
    }
}
