<?php
namespace App\Modules\Catalogs\UI;

use App\Livewire\Shared\RecordEditor;
use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Catalogs\Queries\{CatalogQuery, CatalogOptions};
use App\Modules\Catalogs\Actions\SaveCatalog;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

class CatalogPage extends RecordEditor
{
    use WithFileUploads, ManagesManufacturerLogo;
    #[Locked] public string $catalog;
    #[Locked] public int $lockVersion = 0;
    #[Locked] public string $code = '';
    #[Locked] public string $activeFilter = '';
    public string $name = '';
    public string $distributor = '';
    public string $origin = '';
    public string $description = '';
    public string $visits_recommended_per_year = '';
    public string $country = '';
    public string $website = '';
    public string $notes = '';
    public string $manufacturer_id = '';
    public string $brand_id = '';
    public bool $active = true;
    public string $parentSearch = '';

    public function boot(): void { if (isset($this->catalog)) { Permissions::authorize($this->catalog); } }
    public function mount(string $catalog = ''): void
    {
        Catalogs::get($catalog); $this->catalog = $catalog; Permissions::authorize($catalog); parent::mount();
        if (request()->filled('new_for')) { $this->startRelatedDraft(); }
        if (request()->filled('record')) {
            $id = filter_var(request('record'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); $this->edit($id);
        }
    }
    protected function startRelatedDraft(): void
    {
        abort_if(request()->filled('record'), 422);
        $parent = CatalogOptions::parent($this->catalog); abort_unless($parent, 404);
        Permissions::authorize($parent); Permissions::authorize($this->catalog, 'manage');
        $id = filter_var(request('new_for'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_unless($id, 404); Catalogs::get($parent)['model']::findOrFail($id);
        $this->create(); $field = $parent === 'brands' ? 'brand_id' : 'manufacturer_id'; $this->$field = (string)$id;
    }
    protected function viewModule(): string { return $this->catalog; }
    protected function modelClass(): string { return Catalogs::get($this->catalog)['model']; }
    protected function editableFields(): array { return Catalogs::configuration($this->catalog, 'Fields')::names(); }
    protected function defaultViews(): array { return [['name' => 'Todos', 'filter' => ''], ['name' => 'Activos', 'filter' => '1'], ['name' => 'Inactivos', 'filter' => '0']]; }
    protected function applyViewFilter(string $value): void { $this->activeFilter = $value; }
    protected function viewFields(): array
    {
        $columns = ['code' => 'Código', 'name' => 'Nombre', 'active' => 'Estado'];
        $parent = CatalogOptions::parent($this->catalog);
        if ($parent && Permissions::allows($parent)) { $columns['parent'] = Catalogs::get($parent)['single']; }
        return [...$columns, 'updated_at' => 'Actualizado el'];
    }
    protected function tableColumns(): array
    {
        $columns = [];
        foreach ($this->viewFields() as $key => $label) {
            $columns[$key] = ['field' => match ($key) { 'parent' => ($this->catalog === 'brands' ? 'manufacturer.name' : 'brand.name'), default => $key },
                'filterable' => true, 'sortable' => true, 'groupable' => in_array($key, ['active', 'parent']), 'kind' => $key === 'updated_at' ? 'date' : ($key === 'active' ? 'category' : 'text')];
        }
        $columns['active']['labels'] = [1 => 'Activo', 0 => 'Inactivo'];
        return $columns;
    }
    public function resetColumns(): void { $this->visibleColumns = array_keys($this->viewFields()); $this->saveViewSettings(); }
    public function create(): void { Permissions::authorize($this->catalog, 'manage'); parent::create(); }
    public function edit(int $id): void { Permissions::authorize($this->catalog); parent::edit($id); }
    protected function fillRecord(Model $record): void
    {
        foreach ($this->editableFields() as $field) { $this->$field = $field === 'active' ? (bool)$record->$field : (string)($record->$field ?? ''); }
        $this->lockVersion = $record->lock_version; $this->code = $record->code;
        $this->logoUpload = null; $this->pendingLogoRemoval = []; $this->logoNotice = ''; $this->parentSearch = '';
    }
    protected function resetRecord(): void
    {
        $this->reset($this->editableFields()); $this->code = ''; $this->lockVersion = 0;
        $this->logoUpload = null; $this->pendingLogoRemoval = []; $this->logoNotice = ''; $this->parentSearch = '';
    }
    public function cancel(): void { parent::cancel(); $this->logoUpload = null; $this->pendingLogoRemoval = []; }
    protected function persistRecord(): Model
    {
        return app(SaveCatalog::class)->execute($this->catalog, [...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }
    protected function storeCurrentRecord(): Model
    {
        $record = parent::storeCurrentRecord(); $this->lockVersion = $record->lock_version; $this->code = $record->code; return $record;
    }
    protected function removeRecord(int $id): void { abort(403, 'Inactiva el registro para conservar sus relaciones.'); }
    public function requestOpenRecordDelete(): void { abort(403); }
    public function render()
    {
        $definition = Catalogs::get($this->catalog); $parent = CatalogOptions::parent($this->catalog);
        $items = app(CatalogQuery::class)->records($this->catalog, $this->search, $this->activeFilter);
        $record = $this->formOpen && $this->editingId ? $this->modelClass()::findOrFail($this->editingId) : null;
        return view('catalogs::page', array_merge(compact('items', 'record', 'definition', 'parent'), $this->operationViewsData(), $this->prepareTable($items), [
            'ninoxOrigin' => $record && in_array($this->catalog, ['brands', 'equipment_models']) ? $record->ninoxImport?->load('assets') : null,
            'editorTabs' => Catalogs::configuration($this->catalog, 'Tabs')::definitions(),
            'editorFields' => Catalogs::configuration($this->catalog, 'Fields')::definitions(),
            'editorLayoutStyle' => Catalogs::configuration($this->catalog, 'Layout')::styles(),
            'canManage' => Permissions::allows($this->catalog, 'manage'),
            'parentOptions' => $this->formOpen && $parent ? CatalogOptions::get($parent, $this->parentSearch, $parent === 'brands' ? $this->brand_id : $this->manufacturer_id) : [],
            'related' => $record ? app(CatalogQuery::class)->related($this->catalog, $record->id) : [],
        ]))->layout('components.layouts.app');
    }
}
