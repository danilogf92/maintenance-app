<?php
namespace App\Modules\MaintenancePlans\UI;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\MaintenancePlans\Actions\SaveVisit;
use Livewire\Attributes\Locked;
trait EditsVisits
{
    #[Locked] public ?int $editVisitId = null;
    #[Locked] public int $visitVersion = 0;
    #[Locked] public bool $dailyVisit=false;
    public string $scheduled_for='';
    public string $scheduled_month = '', $visitTechnician = '', $visitNotes = '', $visitStatus = 'planned';
    public function editVisit(int $id): void
    {
        Permissions::authorize('manage'); $visit = PlanQuery::scoped()->findOrFail($this->planId)->visits()->findOrFail($id);
        $this->editVisitId = $id; $this->visitVersion = $visit->lock_version;
        $this->dailyVisit=$visit->schedule_precision==='day'; $this->scheduled_for=$visit->scheduled_for->format('Y-m-d');
        $this->scheduled_month = (string)$visit->scheduled_month; $this->visitTechnician = (string)$visit->technician_id;
        $this->visitNotes = (string)$visit->notes; $this->visitStatus = $visit->status; $this->notice = ''; $this->resetValidation();
    }
    public function cancelEdit(): void { $this->editVisitId = null; $this->resetValidation(); }
    public function saveVisit(): void
    {
        abort_unless($this->editVisitId, 422);
        app(SaveVisit::class)->execute($this->planId, $this->editVisitId, ['scheduled_month' => $this->scheduled_month, 'scheduled_for'=>$this->scheduled_for,
            'technician_id' => $this->visitTechnician, 'notes' => $this->visitNotes, 'status' => $this->visitStatus], $this->visitVersion);
        $this->cancelEdit(); $this->notice = 'Visita actualizada. Se conserva la frecuencia original del plan.';
    }
}
