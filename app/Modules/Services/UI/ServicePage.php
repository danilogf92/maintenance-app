<?php

namespace App\Modules\Services\UI;

use App\Modules\Workflow\UI\TabbedRecordPage;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Services\Configuration\{Columns, TableColumns};
use App\Modules\Services\Queries\{ServiceQuery, ServiceOptions};
use App\Modules\Services\Actions\SaveService;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class ServicePage extends TabbedRecordPage
{
    public string $ticket_id = '';
    public string $service_date = '';
    public string $service_type = 'Mantenimiento preventivo';
    public string $status = 'draft';
    public string $summary = '';
    public string $recommendations = '';
    public string $ticketSearch = '';
    #[Locked] public string $statusFilter = '';
    #[Locked] public bool $relationsFrozen = false;

    protected function viewModule(): string { return 'services'; }
    protected function relatedModule(): string { return 'activities'; }
    protected function relatedModules(): array { return ['activities', 'technical_reports', 'maintenance_protocols']; }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->statusFilter = $value; }

    public function mount(): void
    {
        parent::mount();
        if (request()->filled('new_for_ticket')) {
            abort_if(request()->filled('record'), 422); Permissions::authorize('services', 'manage');
            $id = filter_var(request('new_for_ticket'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); VisibleRecords::query('tickets')->findOrFail($id);
            $this->create(); $this->ticket_id = (string)$id;
        }
    }

    protected function viewFields(): array
    {
        $columns = Columns::columns();
        foreach (['ticket' => 'tickets', 'activities' => 'activities', 'reports' => 'technical_reports'] as $key => $module) {
            if (!Permissions::allows($module)) { unset($columns[$key]); }
        }
        if (!Permissions::allows('tickets') || !Permissions::allows('equipment')) { unset($columns['equipment']); }
        return $columns;
    }

    protected function tableColumns(): array { return array_intersect_key(TableColumns::definitions(), $this->viewFields()); }
    public function resetColumns(): void
    {
        $this->visibleColumns = array_values(array_intersect(['code', 'service_type', 'status', 'service_date', 'ticket', 'technician'], array_keys($this->viewFields())));
        $this->saveViewSettings();
    }

    protected function fillRecord(Model $record): void
    {
        parent::fillRecord($record); $this->ticketSearch = '';
        $this->relationsFrozen = Permissions::allows('services', 'manage') && ($record->activities()->exists() || $record->reports()->exists());
    }

    protected function resetRecord(): void
    {
        parent::resetRecord(); $this->ticketSearch = ''; $this->relationsFrozen = false;
        $this->service_date = today()->format('Y-m-d');
    }

    protected function persistRecord(): Model
    {
        return app(SaveService::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }

    public function render()
    {
        $items = app(ServiceQuery::class)->records($this->search, $this->statusFilter);
        $ninox = \App\Modules\Services\Queries\NinoxDetails::forService($this->formOpen ? $this->editingId : null);
        return view('services::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), ServiceEditor::configuration(), [
            'canManage' => Permissions::allows('services', 'manage') && !$ninox,
            'ninoxDetails' => $ninox,
            'ticketOptions' => $this->formOpen ? ServiceOptions::tickets($this->ticketSearch, $this->ticket_id) : [],
            'serviceContext' => $this->formOpen ? ServiceOptions::context($this->ticket_id, $this->editingId) : [],
        ]))->layout('components.layouts.app');
    }
}
