<?php
namespace App\Modules\MaintenanceProtocols\UI;
use App\Livewire\Shared\RecordEditor;
use App\Modules\MaintenanceProtocols\Configuration\{Fields, Tabs, Layout, Columns, TableColumns, Permissions};
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolStep};
use App\Modules\MaintenanceProtocols\Queries\ProtocolQuery;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
class ProtocolPage extends RecordEditor
{
    #[Locked] public string $name = '', $code = '', $version = '', $type = '', $model_info = '', $status_label = '', $description = '';
    public bool $showPending = false;
    public function boot(): void { Permissions::authorize(); }
    protected function viewModule(): string { return 'maintenance_protocols'; }
    protected function modelClass(): string { return MaintenanceProtocol::class; }
    protected function editableFields(): array { return []; }
    protected function viewFields(): array { return Columns::columns(); }
    protected function tableColumns(): array { return TableColumns::definitions(); }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void {}
    public function mount(): void
    {
        parent::mount();
        if (request()->filled('record')) {
            $id = filter_var(request('record'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); $this->edit($id);
        }
    }
    public function edit(int $id): void { Permissions::authorize(); $this->showPending = false; parent::edit($id); }
    public function create(): void { abort(403); }
    protected function fillRecord(Model $record): void
    {
        foreach (Fields::names() as $field) { $this->$field = (string)($record->$field ?? ''); }
        $this->status_label = $record->is_obsolete === null ? 'Por verificar' : ($record->is_obsolete ? 'Obsoleto' : 'Vigente');
    }
    protected function resetRecord(): void { $this->reset(Fields::names()); }
    protected function persistRecord(): Model { abort(403); }
    protected function removeRecord(int $id): void { abort(403); }
    public function requestOpenRecordDelete(): void { abort(403); }
    public function resetColumns(): void { $this->visibleColumns = ['name', 'code', 'version', 'type', 'steps_count']; $this->saveViewSettings(); }
    public function render()
    {
        $query = app(ProtocolQuery::class); $items = $query->records($this->search);
        return view('maintenance-protocols::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), [
            'editorTabs' => Tabs::definitions(), 'editorFields' => Fields::definitions(), 'editorLayoutStyle' => Layout::styles(),
            'canManage' => false, 'canViewRelation' => false, 'protocolDetails' => $query->details($this->formOpen ? $this->editingId : null),
            'pendingCount' => ProtocolStep::whereNull('maintenance_protocol_id')->count(),
            'pendingSteps' => $this->showPending ? $query->pending() : collect(),
        ]))->layout('components.layouts.app');
    }
}
