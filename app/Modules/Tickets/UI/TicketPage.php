<?php

namespace App\Modules\Tickets\UI;

use App\Modules\Workflow\UI\TabbedRecordPage;
use App\Modules\Workflow\Configuration\{Permissions, FormFields};
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Tickets\Configuration\{Columns, TableColumns};
use App\Modules\Tickets\Queries\{TicketQuery, TicketOptions};
use App\Modules\Tickets\Actions\SaveTicket;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class TicketPage extends TabbedRecordPage
{
    public string $origin = 'internal', $requester_contact_id = '', $reported_by = '';
    public string $subject = '';
    public string $description = '';
    public string $priority = 'normal';
    public string $status = 'open';
    public string $opened_at = '';
    public string $equipment_id = '';
    public string $technician_id = '';
    public string $equipmentSearch = '';
    #[Locked] public string $statusFilter = '';
    #[Locked] public bool $relationsFrozen = false;

    protected function viewModule(): string { return 'tickets'; }
    protected function relatedModule(): string { return 'services'; }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->statusFilter = $value; }

    public function mount(): void
    {
        parent::mount();
        if (request()->filled('new_for_equipment')) {
            abort_if(request()->filled('record'), 422);
            Permissions::authorize('tickets', 'manage');
            $id = filter_var(request('new_for_equipment'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); VisibleRecords::query('equipment')->findOrFail($id);
            $this->create(); $this->equipment_id = (string) $id;
        }
    }

    protected function viewFields(): array
    {
        $columns = Columns::columns();
        if (! Permissions::allows('equipment')) { unset($columns['equipment']); }
        if (! Permissions::allows('services')) { unset($columns['services']); }
        return $columns;
    }

    protected function tableColumns(): array { return array_intersect_key(TableColumns::definitions(), $this->viewFields()); }

    public function resetColumns(): void
    {
        $this->visibleColumns = array_values(array_intersect(['subject', 'code', 'status', 'priority', 'equipment', 'technician'], array_keys($this->viewFields())));
        $this->saveViewSettings();
    }

    protected function fillRecord(Model $record): void
    {
        parent::fillRecord($record); $this->equipmentSearch = '';
        $this->relationsFrozen = Permissions::allows('tickets', 'manage') && $record->services()->exists();
    }

    protected function resetRecord(): void
    {
        parent::resetRecord(); $this->equipmentSearch = ''; $this->relationsFrozen = false;
        $this->opened_at = now()->format('Y-m-d\TH:i');
        $this->technician_id = array_key_exists(auth()->id(), FormFields::technicians()) ? (string) auth()->id() : '';
    }

    protected function persistRecord(): Model
    {
        return app(SaveTicket::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }

    public function render()
    {
        $items = app(TicketQuery::class)->records($this->search, $this->statusFilter);
        $ninox = \App\Modules\Tickets\Queries\NinoxTicketDetails::get($this->formOpen ? $this->editingId : null);
        return view('tickets::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), TicketEditor::configuration(), [
            'ninoxDetails' => $ninox, 'canManage' => Permissions::allows('tickets', 'manage') && !$ninox, 'canViewServices' => Permissions::allows('services'),
            'equipmentOptions' => $this->formOpen ? TicketOptions::equipment($this->equipmentSearch, $this->equipment_id) : [],
            'requesterOptions' => $this->formOpen ? \App\Modules\Tickets\Queries\RequesterOptions::get($this->equipment_id, $this->editingId) : [],
            'planningContext' => $this->formOpen && $this->editingId ? \App\Modules\MaintenancePlans\Queries\TicketPlanningContext::get($this->editingId) : null,
            'canViewContacts' => Permissions::allows('contacts'),
            'technicianOptions' => $this->formOpen ? TicketOptions::technicians($this->editingId) : []]))->layout('components.layouts.app');
    }
}
