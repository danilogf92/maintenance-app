<?php
namespace App\Modules\MaintenancePlans\Actions;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\Workflow\Actions\SaveRecord;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class GenerateVisitTicket
{
    public function execute(int $planId, int $visitId, int $version)
    {
        Permissions::authorize('manage'); \App\Modules\Workflow\Configuration\Permissions::authorize('tickets', 'manage');
        return DB::transaction(function () use ($planId, $visitId, $version) {
            $plan = PlanQuery::scoped()->lockForUpdate()->findOrFail($planId);
            $visit = $plan->visits()->lockForUpdate()->findOrFail($visitId);
            if ($ticket = $visit->ticket()->first()) { return VisibleRecords::query('tickets')->findOrFail($ticket->id); }
            VisitRules::active($plan);
            if ($visit->lock_version !== $version) { throw ValidationException::withMessages(['planning' => 'La visita cambió. Vuelve a cargarla.']); }
            if ($visit->status !== 'planned') { throw ValidationException::withMessages(['planning' => 'La visita está cancelada.']); }
            if (!$visit->technician_id) { throw ValidationException::withMessages(['planning' => 'Asigna un técnico a la visita antes de generar el ticket.']); }
            $ticket = app(SaveRecord::class)->execute('tickets', ['lock_version' => 0, 'equipment_id' => $plan->equipment_id,
                'technician_id' => $visit->technician_id, 'subject' => mb_substr($plan->name.' · '.\App\Modules\MaintenancePlans\Configuration\Periods::visitLabel($visit), 0, 160),
                'description' => $plan->service_type."\n".($visit->notes ?: $plan->notes), 'priority' => 'normal', 'status' => 'open', 'opened_at' => now()]);
            $ticket->forceFill(['maintenance_visit_id' => $visit->id, 'origin' => 'scheduled'])->save();
            $visit->increment('lock_version'); return $ticket->fresh();
        });
    }
}
