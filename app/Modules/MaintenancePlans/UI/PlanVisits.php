<?php
namespace App\Modules\MaintenancePlans\UI;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\MaintenancePlans\Actions\{GenerateVisits, GenerateVisitTicket};
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\{Component, Attributes\Locked};
class PlanVisits extends Component
{
    use EditsVisits;
    #[Locked] public int $planId, $scheduleVersion = 0;
    public string $generationCount = '4', $notice = '';
    public function boot(): void { Permissions::authorize(); }
    public function mount(int $planId): void { $this->planId = $planId; PlanQuery::scoped()->findOrFail($planId); }
    public function programVisits(): void
    {
        Permissions::authorize('manage'); $this->validate(['generationCount' => 'required|integer|min:1|max:120']);
        $count = app(GenerateVisits::class)->execute($this->planId, (int)$this->generationCount, $this->scheduleVersion);
        $this->notice = $count.' visitas programadas. No se han generado tickets todavía.';
        $this->dispatch('planning-visits-updated');
    }
    public function generateTicket(int $id, int $version): void
    {
        $ticket = app(GenerateVisitTicket::class)->execute($this->planId, $id, $version);
        $this->notice = 'Ticket '.$ticket->code.' disponible para esta visita.'; $this->resetValidation();
    }
    public function render()
    {
        $plan = PlanQuery::scoped()->findOrFail($this->planId); $this->scheduleVersion = $plan->schedule_version;
        $visits = $plan->visits()->with('technician')->withExists(['ticket','completedServices'])->orderBy('scheduled_month')->orderBy('sequence')->get();
        $canViewTickets = WorkflowPermissions::allows('tickets');
        $tickets = $canViewTickets ? VisibleRecords::query('tickets')->whereIn('maintenance_visit_id', $visits->pluck('id'))->get()->keyBy('maintenance_visit_id') : collect();
        $technicians = Permissions::allows('manage') ? \App\Modules\Workflow\Configuration\FormFields::technicians() : [];
        if ($this->editVisitId && $visit = $visits->firstWhere('id', $this->editVisitId)) {
            if ($visit->technician) { $technicians[$visit->technician_id] = $visit->technician->name.($visit->technician->is_active ? '' : ' (inactivo)'); }
        }
        return view('maintenance-plans::visits', compact('plan', 'visits', 'tickets', 'technicians') + [
            'canManage' => Permissions::allows('manage'), 'canGenerate' => Permissions::allows('manage') && WorkflowPermissions::allows('tickets', 'manage')]);
    }
}
