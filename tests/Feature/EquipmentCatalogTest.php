<?php
namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogs\Actions\{SaveCatalog, BackfillEquipmentCatalogs, SaveManufacturerLogo};
use App\Modules\Catalogs\Queries\CatalogOptions;
use App\Modules\Catalogs\UI\CatalogPage;
use App\Modules\Manufacturers\Models\Manufacturer;
use App\Modules\Brands\Models\Brand;
use App\Modules\EquipmentModels\Models\EquipmentModel;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Equipment\UI\EquipmentPage;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class EquipmentCatalogTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }
    private function saveCatalog(string $catalog, array $data, $record = null)
    {
        return app(SaveCatalog::class)->execute($catalog, ['name' => 'Catálogo', 'active' => true,
            'notes' => '', 'lock_version' => $record?->lock_version ?? 0, ...$data], $record?->id);
    }
    private function logo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aP9sAAAAASUVORK5CYII='));
    }
    public function test_native_routes_tabs_and_single_autosaved_draft(): void
    {
        foreach (['manufacturers', 'brands', 'equipment_models'] as $catalog) {
            $this->get(route('operations.'.$catalog))->assertOk()->assertSee('Equipos y catálogos');
        }
        $page = Livewire::test(CatalogPage::class, ['catalog' => 'manufacturers'])->call('create'); $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors('name');
        $page->set('name', 'Fabricante nuevo')->call('autosaveDraft', $session)->assertReturned('saved')->call('autosaveDraft', $session);
        $this->assertSame(1, Manufacturer::count());
        $this->get(route('operations.manufacturers', ['record' => $page->get('editingId')]))->assertOk()->assertSee('Logo del fabricante');
    }
    public function test_legacy_backfill_deduplicates_and_preserves_text_without_guessing_manufacturer(): void
    {
        $f = $this->workflow(); $other = $this->workflow();
        DB::table('equipment')->where('id', $f['equipment']->id)->update(['brand' => '  Marca A  ', 'brand_id' => null, 'equipment_model_id' => null]);
        DB::table('equipment')->where('id', $other['equipment']->id)->update(['brand' => 'marca a', 'model' => 'modelo b', 'brand_id' => null, 'equipment_model_id' => null]);
        $before = $f['equipment']->fresh()->updated_at;
        app(BackfillEquipmentCatalogs::class)->execute(); app(BackfillEquipmentCatalogs::class)->execute();
        $this->assertSame(1, Brand::count()); $this->assertSame(1, EquipmentModel::count()); $this->assertSame(0, Manufacturer::count());
        $this->assertSame('  Marca A  ', $f['equipment']->fresh()->brand);
        $this->assertSame($f['equipment']->fresh()->brand_id, $other['equipment']->fresh()->brand_id);
        $this->assertTrue($before->eq($f['equipment']->fresh()->updated_at));
    }
    public function test_duplicate_names_and_new_model_without_brand_are_rejected(): void
    {
        $this->saveCatalog('manufacturers', ['name' => 'Compañía Uno']);
        $page = Livewire::test(CatalogPage::class, ['catalog' => 'manufacturers'])->call('create')->set('name', ' compañía uno ');
        $page->call('autosaveDraft', $page->get('editorSession'))->assertReturned('invalid');
        $this->assertSame(1, Manufacturer::count());
        $this->expectException(ValidationException::class); $this->saveCatalog('equipment_models', ['name' => 'Modelo sin marca']);
    }
    public function test_model_must_belong_to_selected_brand_and_inactive_selection_is_rejected(): void
    {
        $f = $this->workflow(); $other = $this->saveCatalog('brands', ['name' => 'Otra marca']);
        $model = $this->saveCatalog('equipment_models', ['name' => 'M2', 'brand_id' => $other->id]);
        $page = Livewire::test(EquipmentPage::class)->call('edit', $f['equipment']->id); $session = $page->get('editorSession');
        $page->set('equipment_model_id', (string)$model->id)->call('autosaveRecord', $f['equipment']->id, $session)->assertReturned('invalid')->assertHasErrors('equipment_model_id');
        $this->saveCatalog('brands', ['name' => $other->name, 'active' => false], $other);
        $page->set('brand_id', (string)$other->id)->call('autosaveRecord', $f['equipment']->id, $session)->assertReturned('invalid')->assertHasErrors('brand_id');
        $this->assertArrayNotHasKey($other->id, CatalogOptions::get('brands'));
    }
    public function test_verified_mapping_logo_capture_and_old_snapshot_stability(): void
    {
        Storage::fake('public'); $f = $this->workflow();
        $manufacturer = $this->saveCatalog('manufacturers', ['name' => 'Fabricante verificado']);
        $manufacturer = app(SaveManufacturerLogo::class)->execute($manufacturer->id, $manufacturer->lock_version, $this->logo());
        $brand = $f['equipment']->fresh()->brandRecord;
        $this->saveCatalog('brands', ['name' => $brand->name, 'manufacturer_id' => $manufacturer->id], $brand);
        $report = $this->saveWorkflow('technical_reports', ['service_id' => $f['service']->id,
            'report_template_id' => ReportTemplate::firstOrFail()->id, 'title' => 'Informe con fabricante']);
        $this->assertSame($manufacturer->name, $report->snapshot['data']['equipment']['manufacturer']);
        $this->assertStringStartsWith('data:image/png;base64,', $report->snapshot['branding']['images'][0]['factory']);
        app(SaveManufacturerLogo::class)->execute($manufacturer->id, $manufacturer->lock_version);
        $this->assertNotNull($report->fresh()->snapshot['branding']['images'][0]['factory']);
        $updated = app(SaveRecord::class)->execute('technical_reports', $report->only(['service_id', 'report_template_id', 'title', 'lock_version']), $report->id);
        $this->assertNull($updated->snapshot['branding']['images'][0]['factory']);
    }
    public function test_catalog_renaming_syncs_equipment_and_invalidates_old_editor_lock(): void
    {
        $f = $this->workflow(); $equipment = $f['equipment']->fresh(); $brand = $equipment->brandRecord;
        $page = Livewire::test(EquipmentPage::class)->call('edit', $equipment->id); $session = $page->get('editorSession');
        $this->saveCatalog('brands', ['name' => 'Marca corregida', 'manufacturer_id' => null], $brand);
        $this->assertSame('Marca corregida', $equipment->fresh()->brand);
        $page->set('location', 'Cambio atrasado')->call('autosaveRecord', $equipment->id, $session)->assertReturned('invalid')->assertHasErrors('lock_version');
    }
    public function test_reparenting_used_model_and_brand_is_protected(): void
    {
        $f = $this->workflow(); $brand = $f['equipment']->fresh()->brandRecord;
        $manufacturer = $this->saveCatalog('manufacturers', ['name' => 'F1']);
        $brand = $this->saveCatalog('brands', ['name' => $brand->name, 'manufacturer_id' => $manufacturer->id], $brand);
        $other = $this->saveCatalog('manufacturers', ['name' => 'F2']);
        $this->expectException(ValidationException::class);
        $this->saveCatalog('brands', ['name' => $brand->name, 'manufacturer_id' => $other->id], $brand);
    }
    public function test_readers_cannot_create_modify_or_upload_logo_and_no_delete_is_allowed(): void
    {
        $manufacturer = $this->saveCatalog('manufacturers', ['name' => 'F']);
        $reader = User::factory()->create(); $reader->assignRole('CEO'); $this->actingAs($reader);
        Livewire::test(CatalogPage::class, ['catalog' => 'manufacturers'])->call('edit', $manufacturer->id)->assertSee('Solo lectura')->call('create')->assertForbidden();
        Livewire::test(CatalogPage::class, ['catalog' => 'manufacturers'])->call('edit', $manufacturer->id)->call('attachLogo', $manufacturer->id, 'bad')->assertForbidden();
        Livewire::test(CatalogPage::class, ['catalog' => 'manufacturers'])->call('requestOpenRecordDelete')->assertForbidden();
    }
    public function test_stale_logo_upload_cannot_attach_to_another_record(): void
    {
        Storage::fake('public'); $one = $this->saveCatalog('manufacturers', ['name' => 'F1']); $two = $this->saveCatalog('manufacturers', ['name' => 'F2']);
        $page = Livewire::test(CatalogPage::class, ['catalog' => 'manufacturers'])->call('edit', $one->id); $session = $page->get('editorSession');
        $page->call('edit', $two->id)->set('logoUpload', $this->logo())->call('attachLogo', $one->id, $session);
        $this->assertNull($one->fresh()->logo_path); $this->assertNull($two->fresh()->logo_path);
    }
    public function test_catalog_permission_and_parent_permission_are_enforced(): void
    {
        $user = User::factory()->create(); $this->actingAs($user);
        $this->get(route('operations.manufacturers'))->assertForbidden();
        $user->givePermissionTo(['workflow.access', 'brands.view', 'brands.manage']);
        $this->assertSame([], CatalogOptions::get('manufacturers'));
        Livewire::test(CatalogPage::class, ['catalog' => 'brands'])->assertDontSee('Fabricante proporciona');
        $this->actingAs($this->admin); $manufacturer = $this->saveCatalog('manufacturers', ['name' => 'F']); $this->actingAs($user);
        Livewire::test(CatalogPage::class, ['catalog' => 'brands'])->call('create')->set('name', 'Marca')->set('manufacturer_id', (string)$manufacturer->id)
            ->call('autosaveDraft', 'irrelevant')->assertReturned('stale');
        $page = Livewire::test(CatalogPage::class, ['catalog' => 'brands'])->call('create')->set('name', 'Marca')->set('manufacturer_id', (string)$manufacturer->id);
        $page->call('autosaveDraft', $page->get('editorSession'))->assertForbidden();
    }
    public function test_contextual_catalog_drafts_validate_parent_and_do_not_create_records(): void
    {
        $manufacturer = $this->saveCatalog('manufacturers', ['name' => 'F']);
        $this->get(route('operations.brands', ['new_for' => $manufacturer->id]))->assertOk()->assertSee('Nuevo registro');
        $this->assertSame(0, Brand::count());
        $this->get(route('operations.brands', ['new_for' => 'bad']))->assertNotFound();
        $this->get(route('operations.brands', ['new_for' => $manufacturer->id, 'record' => 1]))->assertUnprocessable();
    }
    public function test_equipment_cannot_select_catalog_ids_without_catalog_read_permission(): void
    {
        $f = $this->workflow(); $brand = $this->saveCatalog('brands', ['name' => 'Otra marca']);
        $user = User::factory()->create(); $user->givePermissionTo(['workflow.access', 'equipment.view', 'equipment.manage', 'clients.view']);
        $this->actingAs($user);
        $page = Livewire::test(EquipmentPage::class)->call('edit', $f['equipment']->id)->set('brand_id', (string)$brand->id);
        $page->call('autosaveRecord', $f['equipment']->id, $page->get('editorSession'))->assertForbidden();
    }
    public function test_forged_logo_paths_and_svg_uploads_are_not_accepted(): void
    {
        Storage::fake('public');
        $manufacturer = $this->saveCatalog('manufacturers', ['name' => 'F', 'logo_path' => 'report-assets/not-owned.png']);
        $this->assertNull($manufacturer->logo_path);
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $this->expectException(ValidationException::class);
        app(SaveManufacturerLogo::class)->execute($manufacturer->id, $manufacturer->lock_version, $svg);
    }
    public function test_same_model_name_is_allowed_in_different_brands(): void
    {
        $a = $this->saveCatalog('brands', ['name' => 'A']); $b = $this->saveCatalog('brands', ['name' => 'B']);
        $this->saveCatalog('equipment_models', ['name' => 'X', 'brand_id' => $a->id]);
        $this->saveCatalog('equipment_models', ['name' => 'X', 'brand_id' => $b->id]);
        $this->assertSame(2, EquipmentModel::count());
        $this->assertCount(1, CatalogOptions::get('equipment_models', '', '', $a->id));
    }
}
