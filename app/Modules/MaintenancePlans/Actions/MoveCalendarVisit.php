<?php
namespace App\Modules\MaintenancePlans\Actions;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class MoveCalendarVisit
{
    public function execute(int $visitId, string $month, int $version): void
    {
        Permissions::authorize('manage');
        DB::transaction(function () use ($visitId, $month, $version) {
            $plan = PlanQuery::scoped()->whereHas('visits', fn ($q) => $q->whereKey($visitId))->lockForUpdate()->firstOrFail();
            $visit = $plan->visits()->lockForUpdate()->findOrFail($visitId);
            if (!$plan->active || $visit->status === 'cancelled') {
                throw ValidationException::withMessages(['planning' => 'Solo se pueden mover visitas programadas de planes activos.']);
            }
            \Illuminate\Support\Facades\Validator::make(['scheduled_month'=>$month], ['scheduled_month'=>'required|date_format:Y-m'])->validate();
            $target = \Carbon\CarbonImmutable::parse($month.'-01');
            $date = $target->day(min($visit->scheduled_for->day,$target->daysInMonth))->format('Y-m-d');
            app(SaveVisit::class)->execute($plan->id, $visitId, ['scheduled_month' => $month, 'scheduled_for' => $date,
                'technician_id' => $visit->technician_id, 'status' => $visit->status, 'notes' => $visit->notes], $version);
        });
    }
}
