<?php
namespace App\Modules\MaintenancePlans\UI;
use App\Modules\MaintenancePlans\Configuration\{Permissions, Periods};
use App\Modules\MaintenancePlans\Queries\{CalendarQuery, PlanQuery};
use App\Modules\MaintenancePlans\Actions\MoveCalendarVisit;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use Livewire\{Component, Attributes\Locked, Attributes\Url};
class CalendarPage extends Component
{
    use EditsVisits { saveVisit as private saveVisitForm; }
    #[Url] public int $year;
    #[Url] public string $search = '';
    #[Url] public string $origin='all', $viewMode='year', $anchor='';
    #[Url] public ?int $contractId=null;
    #[Locked] public ?int $selectedVisitId = null;
    #[Locked] public int $planId = 0;
    public string $notice = '';
    public function boot(): void { Permissions::authorize(); }
    public function mount(): void { if (!isset($this->year)) { $this->year = (int)now()->year; } if (!$this->anchor) { $this->anchor=today()->format('Y-m-d'); } $this->validateYear(); }
    public function updatedYear(): void { $this->validateYear(); $this->anchor=sprintf('%04d-01-01',$this->year); $this->closeVisit(); }
    private function validateYear(): void { \Illuminate\Support\Facades\Validator::make(['year' => $this->year], ['year' => 'required|integer|min:1900|max:2200'])->validate(); }
    public function changeYear(int $offset): void
    {
        abort_unless(in_array($offset, [-1, 1]), 422); $this->year = max(1900, min(2200, $this->year + $offset)); $this->anchor=sprintf('%04d-01-01',$this->year); $this->closeVisit();
    }
    public function currentYear(): void { $this->year = (int)now()->year; $this->anchor=today()->format('Y-m-d'); $this->closeVisit(); }
    public function updatedOrigin(): void { $this->contractId=null; $this->closeVisit(); }
    public function updatedContractId(): void { if ($this->contractId) { $this->origin='contract'; } $this->closeVisit(); }
    public function updatedViewMode(): void { $this->closeVisit(); }
    public function updatedAnchor(): void { $this->year=(int)substr($this->anchor,0,4); $this->closeVisit(); }
    public function closeVisit(): void { $this->selectedVisitId = null; $this->cancelEdit(); }
    public function selectVisit(int $id): void
    {
        $plan = PlanQuery::scoped()->whereHas('visits', fn ($q) => $q->whereKey($id))->firstOrFail();
        $visit = $plan->visits()->findOrFail($id); $this->cancelEdit(); $this->planId = $plan->id;
        $this->selectedVisitId = $id; $this->notice = '';
        if (Permissions::allows('manage') && $plan->active && (!$plan->contract_coverage_id || $plan->coverage?->contract?->active) && !$visit->ticket()->exists()) { $this->editVisit($id); }
    }
    public function saveVisit(): void
    {
        $plan = PlanQuery::scoped()->findOrFail($this->planId);
        abort_unless($plan->active, 422); $this->saveVisitForm();
    }
    public function moveVisit(int $id, string $month, int $version): void
    {
        app(MoveCalendarVisit::class)->execute($id, $month, $version);
        $this->closeVisit(); $this->notice = 'Visita reprogramada para '.Periods::label($month).'. Técnico e indicaciones conservados.';
    }
    public function render()
    {
        $this->validateYear();
        \Illuminate\Support\Facades\Validator::make(['origin'=>$this->origin,'viewMode'=>$this->viewMode,'anchor'=>$this->anchor,'contractId'=>$this->contractId], ['origin'=>'required|in:all,contract,equipment','viewMode'=>'required|in:year,month,week,day',
            'anchor'=>'required|date_format:Y-m-d|after_or_equal:1900-01-01|before:2201-01-01','contractId'=>'nullable|integer|min:1'])->validate();
        $range=\App\Modules\MaintenancePlans\Configuration\CalendarRange::make($this->year,$this->viewMode,$this->anchor);
        $data = app(CalendarQuery::class)->execute($this->year,$this->search,$this->origin,$this->contractId,$range);
        $contractOptions=\App\Modules\Contracts\Configuration\Permissions::allows() ? \App\Modules\Contracts\Queries\ContractQuery::scoped()->orderBy('number')->pluck('number','id')->all() : [];
        $selected = $this->selectedVisitId ? PlanQuery::scoped()->findOrFail($this->planId)->visits()
            ->with(['plan.equipment','plan.coverage.contract', 'technician'])->withExists(['ticket','completedServices'])->findOrFail($this->selectedVisitId) : null;
        $ticket = $selected && WorkflowPermissions::allows('tickets') ? VisibleRecords::query('tickets')->where('maintenance_visit_id', $selected->id)->first() : null;
        $technicians = $this->editVisitId ? \App\Modules\Workflow\Configuration\FormFields::technicians() : [];
        if ($selected?->technician) { $technicians[$selected->technician_id] = $selected->technician->name; }
        return view('maintenance-plans::calendar', $data + compact('selected', 'ticket', 'technicians','contractOptions','range') + [
            'canManage' => Permissions::allows('manage'), 'months' => ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
        ])->layout('components.layouts.app');
    }
}
