<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\Brands\Models\Brand;
use App\Modules\EquipmentModels\Models\EquipmentModel;
use App\Modules\Catalogs\Import\{CatalogSource, BrandEvidence, ImportCatalogRecord};
use App\Modules\Catalogs\Models\{NinoxCatalogImport, NinoxCatalogAsset};
use App\Modules\Catalogs\UI\CatalogPage;
use App\Modules\Catalogs\Actions\SaveCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan, Storage};
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class NinoxCatalogImportTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); Storage::fake('local'); }
    private function import(): void { $this->assertSame(0, Artisan::call('ninox:import-catalogs'), Artisan::output()); }
    public function test_real_brands_models_and_images_are_imported_with_evidence(): void
    {
        $this->import(); $this->assertSame(41, Brand::count()); $this->assertSame(53, EquipmentModel::count());
        $this->assertSame(94, NinoxCatalogImport::count()); $this->assertSame(59, NinoxCatalogAsset::where('status','ready')->count());
        $this->assertSame(48, EquipmentModel::whereNotNull('brand_id')->count()); $this->assertSame(5, EquipmentModel::whereNull('brand_id')->count());
        $xtra = NinoxCatalogImport::where('ninox_table','O')->where('ninox_id','22')->firstOrFail();
        $brand = NinoxCatalogImport::where('ninox_table','N')->where('ninox_id','4')->firstOrFail();
        $this->assertSame($brand->record_id, EquipmentModel::findOrFail($xtra->record_id)->brand_id);
        $this->assertSame('shared_equipment', $xtra->brand_basis);
        $this->assertSame(0, Brand::whereNotNull('manufacturer_id')->count());
        $this->assertStringNotContainsString('model_name', $xtra->getRawOriginal('payload'));
    }
    public function test_repeat_import_preserves_manual_changes_and_does_not_duplicate(): void
    {
        $this->import(); $record = Brand::firstOrFail();
        app(SaveCatalog::class)->execute('brands', ['name' => 'Nombre corregido por usuario','active' => false,
            'lock_version' => $record->lock_version, 'notes' => 'Nota propia', 'manufacturer_id' => null, 'origin' => 'Origen corregido'], $record->id);
        $this->import(); $this->assertSame(94, NinoxCatalogImport::count()); $this->assertSame(59, NinoxCatalogAsset::count());
        $record->refresh(); $this->assertSame('Nombre corregido por usuario', $record->name);
        $this->assertFalse($record->active); $this->assertSame('Origen corregido', $record->origin);
    }
    public function test_brand_inference_requires_every_equipment_to_have_one_consistent_brand(): void
    {
        $evidence=app(BrandEvidence::class); $brands=[['ninox_id'=>'1','equipment_id'=>[100,101]],['ninox_id'=>'2','equipment_id'=>[102]]];
        $this->assertSame('1',$evidence->candidates($brands,['equipments'=>[100,101]]));
        $this->assertNull($evidence->candidates($brands,['equipments'=>[]]));
        $this->assertNull($evidence->candidates($brands,['equipments'=>[100,999]]));
        $this->assertNull($evidence->candidates($brands,['equipments'=>[100,102]]));
        $brands[]=['ninox_id'=>'3','equipment_id'=>[100]];
        $this->assertNull($evidence->candidates($brands,['equipments'=>[100]]));
    }
    public function test_model_and_brand_native_tabs_show_originals_and_edit_new_fields(): void
    {
        $this->import(); $brand = Brand::firstOrFail();
        Livewire::test(CatalogPage::class,['catalog'=>'brands'])->call('edit',$brand->id)
            ->assertSee('Datos comerciales')->assertSee('Detalles')->assertSee('Proveedor registrado');
        $model = EquipmentModel::whereNotNull('brand_id')->firstOrFail();
        $page = Livewire::test(CatalogPage::class,['catalog'=>'equipment_models'])->call('edit',$model->id)
            ->assertSee('Datos del modelo')->assertSee('unidad de estos tiempos');
        $page->set('visits_recommended_per_year','2')->set('description','Descripción ajustada')
            ->call('autosaveRecord',$model->id,$page->get('editorSession'))->assertReturned('saved');
        $this->assertSame(2,(int)$model->fresh()->visits_recommended_per_year);
        $page->set('visits_recommended_per_year','')->call('autosaveRecord',$model->id,$page->get('editorSession'))->assertReturned('saved');
        $this->assertNull($model->fresh()->visits_recommended_per_year);
    }
    public function test_images_require_permission_and_matching_catalog_record(): void
    {
        $this->import(); $import=NinoxCatalogImport::where('catalog','brands')->whereHas('assets')->firstOrFail();$asset=$import->assets()->firstOrFail();
        $url=route('catalogs.asset',['brands',$import->record_id,$asset->id]);
        $this->get($url)->assertOk()->assertHeader('Content-Type',$asset->mime);
        $this->get(route('catalogs.asset',['equipment_models',$import->record_id,$asset->id]))->assertNotFound();
        $user=User::factory()->create();$this->actingAs($user);$this->get($url)->assertForbidden();
        auth()->logout();$this->get($url)->assertRedirect(route('login'));
    }
    public function test_code_name_and_changed_source_conflicts_do_not_overwrite(): void
    {
        $root=base_path('database/imports/ninox-catalogs/brands');$data=app(CatalogSource::class)->read($root,'brands')[0];
        $action=app(ImportCatalogRecord::class);$action->execute('brands',$data,null,$root);
        $changed=[...$data,'origin'=>'Different origin'];$this->assertSame(['conflicts'=>1],$action->execute('brands',$changed,null,$root));
        $other=[...$data,'ninox_id'=>'999'];$this->assertSame(['conflicts'=>1],$action->execute('brands',$other,null,$root));
        $this->assertSame(1,Brand::count());
    }
    public function test_dry_run_validates_without_writing_catalogs(): void
    {
        $this->artisan('ninox:import-catalogs',['--dry-run'=>true])->assertSuccessful();
        $this->assertSame(0,Brand::count());$this->assertSame(0,EquipmentModel::count());
    }
}
