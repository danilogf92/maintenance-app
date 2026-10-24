<?php
namespace App\Modules\MaintenancePlans\UI;
use App\Livewire\Shared\RecordEditor;
use App\Modules\MaintenancePlans\Configuration\{Fields, Columns, TableColumns, Permissions};
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\MaintenancePlans\Actions\SavePlan;
use App\Modules\Tickets\Queries\TicketOptions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
class PlanPage extends RecordEditor
{
    public string $name = '', $equipment_id = '', $technician_id = '', $contract_reference = '', $notes = '';
    public string $service_type = 'Mantenimiento preventivo', $first_month = '', $end_month = '', $interval_months = '3';
    public string $interval_value='3', $interval_unit='months', $first_date='', $end_date='', $expected_visits='', $contract_coverage_id='';
    public string $equipmentSearch = '';
    public bool $active = true;
    #[Locked] public int $lockVersion = 0;
    #[Locked] public string $code = '', $activeFilter = '';
    #[Locked] public array $loadedEditorRelations = [];
    public function boot(): void { Permissions::authorize(); }
    protected function viewModule(): string { return 'maintenance_plans'; }
    protected function modelClass(): string { return MaintenancePlan::class; }
    protected function editableFields(): array { return Fields::names(); }
    protected function viewFields(): array { return Columns::columns(); }
    protected function tableColumns(): array { return TableColumns::definitions(); }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->activeFilter = $value; }
    public function mount(): void
    {
        parent::mount(); abort_if(collect(['record','new_for_equipment','new_for_coverage'])->filter(fn($key)=>request()->filled($key))->count()>1,422);
        if (request()->filled('record')) {
            $id = filter_var(request('record'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); abort_unless($id, 404); $this->edit($id);
        } elseif (request()->filled('new_for_coverage')) {
            $id=filter_var(request('new_for_coverage'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]); abort_unless($id,404);
            $coverage=\App\Modules\Contracts\Models\EquipmentCoverage::whereHas('contract',fn($q)=>$q->whereIn('id',\App\Modules\Contracts\Queries\ContractQuery::scoped()->select('id')))->findOrFail($id);
            VisibleRecords::query('equipment')->findOrFail($coverage->equipment_id); $this->create();
            $this->equipment_id=(string)$coverage->equipment_id; $this->contract_coverage_id=(string)$coverage->id;
            $this->first_month=$coverage->first_month; $this->end_month=$coverage->end_month;
            $this->first_date=$coverage->first_month.'-01'; $this->end_date=\Carbon\CarbonImmutable::parse($coverage->end_month.'-01')->endOfMonth()->format('Y-m-d');
        } elseif (request()->filled('new_for_equipment')) {
            $id = filter_var(request('new_for_equipment'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); abort_unless($id, 404);
            VisibleRecords::query('equipment')->findOrFail($id); $this->create(); $this->equipment_id = (string)$id;
        } elseif (request()->boolean('new')) { $this->create(); }
    }
    public function create(): void
    {
        Permissions::authorize('manage'); $session = $this->editorSession; parent::create();
        if ($session !== $this->editorSession) { $this->loadedEditorRelations = []; }
    }
    public function edit(int $id): void
    {
        PlanQuery::scoped()->findOrFail($id); $session = $this->editorSession; parent::edit($id);
        if ($session !== $this->editorSession) { $this->loadedEditorRelations = []; }
    }
    protected function fillRecord(Model $record): void
    {
        foreach (Fields::definitions() as $field => $definition) {
            $this->$field = match ($definition['type']) { 'checkbox' => (bool)$record->$field, 'date' => $record->$field?->format('Y-m-d') ?? '', default => (string)$record->$field };
        }
        $this->lockVersion = $record->lock_version; $this->code = $record->code; $this->equipmentSearch = '';
    }
    protected function resetRecord(): void
    {
        $this->reset(Fields::names()); $this->lockVersion = 0; $this->code = ''; $this->equipmentSearch = ''; $this->first_month = today()->format('Y-m'); $this->first_date=today()->format('Y-m-d');
    }
    protected function persistRecord(): Model { return app(SavePlan::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId); }
    protected function storeCurrentRecord(): Model
    {
        $record = parent::storeCurrentRecord(); $this->lockVersion = $record->lock_version; $this->code = $record->code; return $record;
    }
    protected function removeRecord(int $id): void { abort(403, 'Inactiva el plan para conservar su historial.'); }
    public function requestOpenRecordDelete(): void { abort(403); }
    public function resetColumns(): void { $this->visibleColumns = ['name', 'code', 'equipment', 'origin', 'first_month', 'status', 'visits']; $this->saveViewSettings(); }
    public function loadEditorRelation(string $relation): void
    {
        abort_unless($relation === 'maintenance_visits', 422); Permissions::authorize();
        if ($this->formOpen && $this->finishCurrentRecord() && $this->editingId) { $this->loadedEditorRelations[$relation] = true; }
    }
    #[\Livewire\Attributes\On('planning-visits-updated')]
    public function refreshVisitState(): void {}

    public function render()
    {
        $items = app(PlanQuery::class)->records($this->search, $this->activeFilter);
        $record = $this->formOpen && $this->editingId ? PlanQuery::scoped()->findOrFail($this->editingId) : null;
        $technicianOptions = $this->formOpen ? \App\Modules\Workflow\Configuration\FormFields::technicians() : [];
        if ($record?->technician) { $technicianOptions[$record->technician_id] = $record->technician->name.($record->technician->is_active ? '' : ' (inactivo)'); }
        return view('maintenance-plans::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), PlanEditor::configuration(),
            ['canManage' => Permissions::allows('manage'), 'hasVisits' => $record?->visits()->exists() ?? false,
                'equipmentOptions' => $this->formOpen ? TicketOptions::equipment($this->equipmentSearch, $this->equipment_id) : [],
                'technicianOptions' => $technicianOptions,
                'coverageOptions'=>$this->formOpen && \App\Modules\Contracts\Configuration\Permissions::allows() ? \App\Modules\Contracts\Models\EquipmentCoverage::whereHas('contract',fn($q)=>$q->whereIn('id',\App\Modules\Contracts\Queries\ContractQuery::scoped()->select('id')))
                    ->when($this->equipment_id !== '',fn($q)=>$q->where('equipment_id',$this->equipment_id))->with(['contract','equipment'])->get()->mapWithKeys(fn($c)=>[$c->id=>$c->contract->number.' · '.$c->equipment->name.' · '.$c->first_month.' a '.$c->end_month])->all() : []]))->layout('components.layouts.app');
    }
}
