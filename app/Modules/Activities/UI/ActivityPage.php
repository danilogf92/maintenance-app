<?php

namespace App\Modules\Activities\UI;

use App\Modules\Workflow\UI\TabbedRecordPage;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Activities\Configuration\{Columns, TableColumns};
use App\Modules\Activities\Queries\{ActivityQuery, ActivityOptions};
use App\Modules\Activities\Actions\SaveActivity;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

class ActivityPage extends TabbedRecordPage
{
    use WithFileUploads, ManagesEvidence;
    public string $service_id = '';
    public string $description = '';
    public string $result = '';
    public string $serviceSearch = '';
    #[Locked] public array $photos = [];
    #[Locked] public string $resultFilter = '';

    protected function viewModule(): string { return 'activities'; }
    protected function relatedModule(): string { return 'services'; }
    protected function relatedModules(): array { return []; }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->resultFilter = $value; }

    public function mount(): void
    {
        parent::mount();
        if (request()->filled('new_for_service')) {
            abort_if(request()->filled('record'), 422); Permissions::authorize('activities', 'manage');
            $id = filter_var(request('new_for_service'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); VisibleRecords::query('services')->findOrFail($id);
            $this->create(); $this->service_id = (string)$id;
        }
    }

    protected function viewFields(): array
    {
        $columns = Columns::columns();
        if (!Permissions::allows('services')) { unset($columns['service'], $columns['technician']); }
        if (!Permissions::allows('services') || !Permissions::allows('tickets') || !Permissions::allows('equipment')) { unset($columns['equipment']); }
        return $columns;
    }

    protected function tableColumns(): array { return array_intersect_key(TableColumns::definitions(), $this->viewFields()); }
    public function resetColumns(): void
    {
        $this->visibleColumns = array_values(array_intersect(['code', 'description', 'result', 'service', 'photos'], array_keys($this->viewFields())));
        $this->saveViewSettings();
    }

    protected function fillRecord(Model $record): void
    {
        parent::fillRecord($record); $this->resetEvidence(); $this->serviceSearch = '';
    }

    protected function resetRecord(): void
    {
        parent::resetRecord(); $this->resetEvidence(); $this->serviceSearch = '';
    }

    public function cancel(): void
    {
        parent::cancel(); $this->resetEvidence(); $this->photos = [];
    }

    protected function persistRecord(): Model
    {
        return app(SaveActivity::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }

    public function render()
    {
        $items = app(ActivityQuery::class)->records($this->search, $this->resultFilter);
        return view('activities::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), ActivityEditor::configuration(), [
            'canManage' => Permissions::allows('activities', 'manage'),
            'serviceOptions' => $this->formOpen ? ActivityOptions::services($this->serviceSearch, $this->service_id) : [],
            'protocolContext' => \App\Modules\MaintenanceProtocols\Queries\ActivityProtocolContext::forActivity($this->formOpen ? $this->editingId : null),
            'activityContext' => $this->formOpen ? ActivityOptions::context($this->service_id) : [],
        ]))->layout('components.layouts.app');
    }
}
