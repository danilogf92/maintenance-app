<?php
namespace App\Modules\DataManagement\UI;
use App\Modules\DataManagement\Actions\{ReadInventory, ReviewDiagnostics, StartCatalogImport, StartEquipmentHistoryImport, CatalogImportDiagnostics};
use App\Modules\DataManagement\Models\DataImportRun;
use App\Modules\DataManagement\Configuration\{Permissions, Entities};
use App\Modules\DataManagement\Queries\{Reconciliation, SchemaReview};
use App\Modules\DataManagement\Support\{InventoryStore, MappingStore, SourceName, CatalogPackage, EquipmentHistoryPackage};
use Filament\Pages\Page;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
class DataManagementPage extends Page
{
    use WithFileUploads;
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Gestión de datos';
    protected static ?string $title = 'Gestión de datos';
    protected static ?string $slug = 'gestion-de-datos';
    protected static ?int $navigationSort = 15;
    protected string $view = 'data-management::page';
    #[Locked] public string $tab = 'overview', $mappingVersion = '', $selected = '', $feedback = '';
    public string $search = '';
    public array $mappings = [];
    public $inventoryUpload;
    public static function canAccess(): bool { return Permissions::allows(); }
    public function mount(): void
    {
        Permissions::authorize();
        $store = app(InventoryStore::class);
        if (!$store->current() && $store->latestPath() && Permissions::allows('manage')) {
            try { $store->loadLatest(); } catch (\Illuminate\Validation\ValidationException $e) { $this->setErrorBag($e->validator->errors()); }
        }
        $this->reloadMappings();
    }
    public function selectTab(string $tab): void
    {
        Permissions::authorize(); abort_unless(in_array($tab, ['overview', 'mappings', 'relations', 'imports', 'diagnostics'], true), 422);
        $this->tab = $tab;
    }
    public function selectSource(string $key): void
    {
        Permissions::authorize();
        abort_unless(collect(app(InventoryStore::class)->current()['tables'] ?? [])->contains(fn ($r) => SourceName::key($r) === $key), 404);
        $this->selected = $key; $this->tab = 'mappings'; $this->dispatch('data-source-selected');
    }
    public function loadLatest(): void
    {
        Permissions::authorize('manage'); $this->feedback = ''; $this->resetValidation();
        app(InventoryStore::class)->loadLatest(); $this->reloadMappings(); $this->selected = '';
        $this->feedback = 'Diagnóstico actualizado. Los datos operativos no se han importado.';
    }
    public function loadUpload(): void
    {
        Permissions::authorize('manage'); $this->feedback = ''; $this->resetValidation();
        $this->validate(['inventoryUpload' => 'required|file|max:10240']);
        $data = app(ReadInventory::class)->execute($this->inventoryUpload->getRealPath());
        app(InventoryStore::class)->save($data); $this->inventoryUpload->delete(); $this->inventoryUpload = null;
        $this->reloadMappings(); $this->selected = ''; $this->feedback = 'Inventario cargado para revisión. Registros importados: 0.';
    }
    public function saveMappings(): void
    {
        Permissions::authorize('manage'); $this->feedback = '';
        $source = app(InventoryStore::class)->current(); abort_unless($source, 422);
        app(MappingStore::class)->save($this->mappings, $source['tables'], $this->mappingVersion);
        $this->reloadMappings(); $this->feedback = 'Correspondencias guardadas en data/correspondencias.json. Las relaciones siguen pendientes de validar.';
    }
    public function reloadMappings(): void
    {
        Permissions::authorize(); $store = app(MappingStore::class); $existing = $store->read(); $this->mappings = [];
        foreach (app(InventoryStore::class)->current()['tables'] ?? [] as $source) {
            $target = $store->resolve($source['name'], $existing); $this->mappings[SourceName::key($source)] = $target ?? '';
        }
        $this->mappingVersion = $store->fingerprint(); $this->resetValidation();
    }
    public function downloadReview()
    {
        Permissions::authorize(); $inventory = app(InventoryStore::class)->current(); abort_unless($inventory, 422);
        $rows = app(Reconciliation::class)->rows($inventory, []);
        $relations = app(SchemaReview::class)->relations(array_values(array_filter(array_column($rows, 'target'))));
        $path = app(ReviewDiagnostics::class)->create($inventory, $rows, $relations);
        return response()->download($path, 'diagnostico-gestion-de-datos-'.now()->format('Ymd-His').'.zip');
    }
    public function startCatalogImport(): void
    {
        Permissions::authorize('manage'); $this->resetValidation(); $this->feedback = '';
        $run = app(StartCatalogImport::class)->execute(); $this->tab = 'imports';
        $this->feedback = 'Carga enviada a la cola. Puedes seguir usando la aplicación. Referencia: '.$run->id;
    }
    public function downloadImport(string $id)
    {
        Permissions::authorize(); abort_unless(\Illuminate\Support\Str::isUuid($id), 404);
        $run = DataImportRun::findOrFail($id);
        return response()->download(app(CatalogImportDiagnostics::class)->create($run), 'resultado-carga-'.$run->id.'.zip');
    }
    public function startHistoryImport(): void
    {
        Permissions::authorize('manage'); $this->resetValidation(); $this->feedback = '';
        $run = app(StartEquipmentHistoryImport::class)->execute(); $this->tab = 'imports'; $this->reloadMappings();
        $this->feedback = 'Historiales enviados a la cola. El resultado indicará qué equipos se vincularon y cuáles requieren revisión. Referencia: '.$run->id;
    }
    protected function getViewData(): array
    {
        Permissions::authorize(); $inventory = app(InventoryStore::class)->current();
        $rows = $inventory ? app(Reconciliation::class)->rows($inventory, $this->mappings) : [];
        $selectedRow = collect($rows)->firstWhere('key', $this->selected);
        $visibleRows = collect($rows)->filter(fn ($r) => $this->search === '' || str_contains(mb_strtolower($r['source']['name'].' '.$r['source']['folder'].' '.($r['target'] ?? '')), mb_strtolower($this->search)));
        $savedMappings = app(MappingStore::class)->read();
        $savedTargets = array_map(fn ($source) => app(MappingStore::class)->resolve($source['name'], $savedMappings), $inventory['tables'] ?? []);
        $relations = $this->tab === 'relations' ? app(SchemaReview::class)->relations(array_values(array_filter($savedTargets))) : [];
        $prepared = null; $preparedError = ''; $historyPrepared = null; $historyError = '';
        if ($this->tab === 'imports') {
            try { $prepared = app(CatalogPackage::class)->latest(false); }
            catch (\Illuminate\Validation\ValidationException $e) { $preparedError = $e->validator->errors()->first(); }
            try { $historyPrepared = app(EquipmentHistoryPackage::class)->latest(false); }
            catch (\Illuminate\Validation\ValidationException $e) { $historyError = $e->validator->errors()->first(); }
        }
        $importRuns = $this->tab === 'imports' ? DataImportRun::latest()->get() : collect();
        return compact('inventory', 'rows', 'visibleRows', 'selectedRow', 'relations', 'prepared', 'preparedError', 'historyPrepared', 'historyError', 'importRuns')
            + ['targets' => app(SchemaReview::class)->metadata(), 'canManage' => Permissions::allows('manage')];
    }
}
