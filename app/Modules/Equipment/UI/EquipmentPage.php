<?php

namespace App\Modules\Equipment\UI;

use App\Modules\Workflow\UI\TabbedRecordPage;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Equipment\Configuration\{Columns, TableColumns};
use App\Modules\Equipment\Queries\{EquipmentQuery, EquipmentOptions};
use App\Modules\Equipment\Actions\SaveEquipment;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class EquipmentPage extends TabbedRecordPage
{
    public string $name = '';
    public string $brand_id = '';
    public string $equipment_model_id = '';
    public string $catalogSearch = '';
    public string $serial = '';
    public string $status = 'operational';
    public string $client_id = '';
    public string $institution_id = '';
    public string $location = '';
    public string $clientSearch = '';
    public string $institutionSearch = '';
    #[Locked] public string $statusFilter = '';
    #[Locked] public bool $relationsFrozen = false;

    protected function viewModule(): string { return 'equipment'; }
    protected function relatedModules(): array { return ['tickets', 'maintenance_plans']; }
    protected function relatedModule(): string { return 'tickets'; }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->statusFilter = $value; }

    protected function viewFields(): array
    {
        $columns = Columns::columns();
        if (! Permissions::allows('clients')) { unset($columns['client']); }
        if (! Permissions::allows('tickets')) { unset($columns['tickets']); }
        return $columns;
    }

    protected function tableColumns(): array { return array_intersect_key(TableColumns::definitions(), $this->viewFields()); }

    public function resetColumns(): void
    {
        $this->visibleColumns = array_values(array_intersect(['name', 'code', 'serial', 'client', 'institution', 'status'], array_keys($this->viewFields())));
        $this->saveViewSettings();
    }

    public function updated(string $property): void
    {
        parent::updated($property);
        if ($property === 'brand_id') { $this->equipment_model_id = ''; }
    }

    protected function fillRecord(Model $record): void
    {
        parent::fillRecord($record); $this->clientSearch = ''; $this->institutionSearch = ''; $this->catalogSearch = '';
        $this->relationsFrozen = Permissions::allows('equipment', 'manage') && ($record->tickets()->exists() || \App\Modules\MaintenancePlans\Models\MaintenancePlan::where('equipment_id', $record->id)->exists());
    }

    protected function resetRecord(): void
    {
        parent::resetRecord(); $this->clientSearch = ''; $this->institutionSearch = ''; $this->catalogSearch = ''; $this->relationsFrozen = false;
    }

    protected function persistRecord(): Model
    {
        return app(SaveEquipment::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }

    public function render()
    {
        $receivedDetails = \App\Modules\Equipment\Queries\ReceivedEquipmentDetails::get($this->formOpen ? $this->editingId : null);
        $items = app(EquipmentQuery::class)->records($this->search, $this->statusFilter);
        return view('equipment::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), EquipmentEditor::configuration(), [
             'brandOptions' => \App\Modules\Catalogs\Queries\CatalogOptions::get('brands', $this->catalogSearch, $this->brand_id),
            'modelOptions' => \App\Modules\Catalogs\Queries\CatalogOptions::get('equipment_models', $this->catalogSearch, $this->equipment_model_id, (int)$this->brand_id ?: null),
            'manufacturerName' => $this->brand_id ? \App\Modules\Brands\Models\Brand::find($this->brand_id)?->manufacturer?->name : null,
            'editorRelations' => ['tickets' => ['allowed' => Permissions::allows('tickets'), 'component' => 'equipment-related-tickets', 'key' => 'equipmentId'],
                'maintenance_plans' => ['allowed' => \App\Modules\MaintenancePlans\Configuration\Permissions::allows(), 'component' => 'equipment-maintenance-plans', 'key' => 'equipmentId']],
            'receivedDetails' => $receivedDetails,
            'canManage' => Permissions::allows('equipment', 'manage') && !$receivedDetails, 'canViewTickets' => Permissions::allows('tickets'),
            'clientOptions' => $this->formOpen ? EquipmentOptions::clients($this->clientSearch, $this->client_id) : [],
            'institutionOptions' => $this->formOpen ? EquipmentOptions::institutions($this->institutionSearch, $this->institution_id) : []]))->layout('components.layouts.app');
    }
}
