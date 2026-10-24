<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\DataManagement\Actions\ReadInventory;
use App\Modules\DataManagement\Queries\{SchemaReview, Reconciliation};
use App\Modules\DataManagement\Support\{InventoryStore, MappingStore, SourceName};
use App\Modules\DataManagement\UI\DataManagementPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class DataManagementTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void
    {
        parent::setUp(); $this->bootWorkflow(); Storage::fake('local');
        $root = Storage::disk('local')->path('configuration'); mkdir($root, 0777, true);
        config(['data-management.mapping_path' => $root.'/correspondencias.json', 'data-management.inventory_root' => $root.'/resultado']);
        copy(base_path('data/correspondencias.json.example'), $root.'/correspondencias.json');
    }
    private function inventory(): array { return app(ReadInventory::class)->execute(base_path('tests/Fixtures/data-management/inventory.json')); }
    private function saveInventory(): array { $inventory = $this->inventory(); app(InventoryStore::class)->save($inventory); return $inventory; }
    public function test_admin_section_shows_real_inventory_separately_from_database_and_relationship_schema(): void
    {
        $this->saveInventory();
        $this->get('/admin/gestion-de-datos')->assertOk()->assertSee('Gestión de datos')->assertSee('37')->assertSee('7.346')->assertSee('2.898')->assertSee('Fase de revisión');
        $page = Livewire::test(DataManagementPage::class)->call('selectTab', 'mappings')->assertSee('Registros en BD')->assertSee('Clientes');
        $inventory = $this->inventory(); $client = collect($inventory['tables'])->firstWhere('name', 'Clientes');
        $page->call('selectSource', SourceName::key($client))->assertSee('client_name_or_business_name')->assertSee('tax_id');
        $page->call('selectTab', 'relations')->assertSee('Relaciones que existen en la base de datos')->assertSee('client_id')->assertSee('Opcional');
        $this->assertDatabaseCount('clients', 0); $this->assertDatabaseCount('equipment', 0);
    }
    public function test_saving_one_correspondence_preserves_other_entries_and_does_not_import_records(): void
    {
        $inventory = $this->saveInventory(); $activity = collect($inventory['tables'])->firstWhere('name', 'Actividades___hallazgos');
        $store = app(MappingStore::class); $existing = $store->read(); $existing['Tabla futura'] = 'future_table';
        file_put_contents($store->path(), json_encode($existing));
        Livewire::test(DataManagementPage::class)->set('mappings.'.SourceName::key($activity), 'activities')->call('saveMappings')
            ->assertHasNoErrors()->assertSee('Correspondencias guardadas');
        $saved = $store->read(); $this->assertSame('activities', $store->resolve('Actividades / hallazgos', $saved));
        $this->assertSame('future_table', $saved['Tabla futura']); $this->assertSame('clients', $store->resolve('Clientes', $saved));
        $this->assertDatabaseCount('activities', 0); $this->assertDatabaseCount('clients', 0);
    }
    public function test_invalid_targets_and_stale_mapping_version_are_rejected(): void
    {
        $inventory = $this->saveInventory(); $source = $inventory['tables'][0]; $key = SourceName::key($source);
        Livewire::test(DataManagementPage::class)->set('mappings.'.$key, 'users')->call('saveMappings')->assertHasErrors('mappings.'.$key);
        $page = Livewire::test(DataManagementPage::class); $store = app(MappingStore::class); $data = $store->read(); $data['Nuevo'] = 'clients'; file_put_contents($store->path(), json_encode($data));
        $page->call('saveMappings')->assertHasErrors('mappings');
        $this->assertSame('clients', $store->read()['Nuevo']);
    }
    public function test_read_only_admin_can_review_but_cannot_change_inventory_or_mappings(): void
    {
        $this->saveInventory(); $user = User::factory()->create(['can_access_admin' => true]);
        $user->givePermissionTo(['panel.access', 'data_management.view']); $this->actingAs($user);
        $this->get('/admin/gestion-de-datos')->assertOk();
        Livewire::test(DataManagementPage::class)->call('saveMappings')->assertForbidden();
        Livewire::test(DataManagementPage::class)->call('loadLatest')->assertForbidden();
        $user->revokePermissionTo('data_management.view'); $this->get('/admin/gestion-de-datos')->assertForbidden();
    }
    public function test_zip_upload_loads_inventory_and_keeps_private_record_values_out_of_saved_snapshot(): void
    {
        $path = Storage::disk('local')->path('source.zip'); $zip = new \ZipArchive; $zip->open($path, \ZipArchive::CREATE);
        $data = json_decode(file_get_contents(base_path('tests/Fixtures/data-management/inventory.json')), true);
        $data['tables'][0]['files'] = [['private_value' => 'DO_NOT_KEEP']];
        $zip->addFromString('carpetas.json', json_encode($data)); $zip->addFromString('errors.json', json_encode(['issues' => []])); $zip->close();
        Livewire::test(DataManagementPage::class)->set('inventoryUpload', UploadedFile::fake()->createWithContent('diagnostico.zip', file_get_contents($path)))
            ->call('loadUpload')->assertHasNoErrors()->assertSee('Inventario cargado');
        $saved = app(InventoryStore::class)->current(); $this->assertCount(37, $saved['tables']); $this->assertStringNotContainsString('DO_NOT_KEEP', json_encode($saved));
    }
    public function test_latest_local_diagnostic_is_automatically_available_in_admin(): void
    {
        $path = config('data-management.inventory_root').'/latest'; mkdir($path, 0777, true);
        copy(base_path('tests/Fixtures/data-management/inventory.json'), $path.'/carpetas.json');
        Livewire::test(DataManagementPage::class)->assertSee('7.346')->assertSee('2.898');
        $this->assertCount(37, app(InventoryStore::class)->current()['tables']);
    }
    public function test_live_relationship_counts_distinguish_missing_links_and_source_presence(): void
    {
        $f = $this->workflow(); $f['equipment']->update(['client_id' => null]);
        $relations = app(SchemaReview::class)->relations(['clients']);
        $row = collect($relations)->first(fn ($r) => $r['child'] === 'equipment' && $r['column'] === 'client_id');
        $this->assertSame(1, $row['unlinked']); $this->assertSame(0, $row['linked']); $this->assertTrue($row['source_parent_present']);
        $rows = app(Reconciliation::class)->rows($this->inventory(), []);
        $visits = collect($rows)->first(fn ($r) => $r['source']['name'] === 'Visita');
        $this->assertNull($visits['target']); $this->assertStringContainsString('No son visitas del calendario', $visits['hint']['note']);
    }
    public function test_invalid_inventory_and_duplicate_folders_do_not_replace_current_review(): void
    {
        $before = $this->saveInventory(); $path = Storage::disk('local')->path('invalid.json');
        file_put_contents($path, 'null'); $this->assertThrows(fn () => app(ReadInventory::class)->execute($path), ValidationException::class);
        $data = $this->inventory(); $data['tables'][] = $data['tables'][0]; file_put_contents($path, json_encode($data));
        $this->assertThrows(fn () => app(ReadInventory::class)->execute($path), ValidationException::class);
        $this->assertSame($before, app(InventoryStore::class)->current());
    }
    public function test_review_download_contains_ack_and_mappings_without_claiming_import(): void
    {
        $this->saveInventory(); Livewire::test(DataManagementPage::class)->call('downloadReview')->assertFileDownloaded();
        $files = Storage::disk('local')->allFiles('data-management/runs'); $ackPath = collect($files)->first(fn ($f) => str_ends_with($f, '/ack.json'));
        $ack = json_decode(Storage::disk('local')->get($ackPath), true); $this->assertFalse($ack['imported']); $this->assertSame('revision', $ack['operation']);
        $this->assertCount(37, $ack['tables']);
    }
    public function test_oversized_zip_inventory_is_rejected_before_decompression(): void
    {
        $path = Storage::disk('local')->path('oversized.zip'); $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::CREATE); $zip->addFromString('carpetas.json', str_repeat(' ', 26 * 1024 * 1024)); $zip->close();
        $this->assertThrows(fn () => app(ReadInventory::class)->execute($path), ValidationException::class);
        $this->assertNull(app(InventoryStore::class)->current());
    }

}
