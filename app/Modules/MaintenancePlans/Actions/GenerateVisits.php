<?php

namespace App\Modules\MaintenancePlans\Actions;

use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Configuration\Schedule;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateVisits
{
    public function execute(int $planId, int $count, int $version): int
    {
        Permissions::authorize('manage');
        abort_unless($count >= 1 && $count <= 120, 422);

        return DB::transaction(function () use ($planId, $count, $version) {
            $plan = PlanQuery::scoped()->lockForUpdate()->findOrFail($planId);
            VisitRules::active($plan);
            if ($plan->schedule_version !== $version) {
                throw ValidationException::withMessages(['planning' => 'El plan cambió. Vuelve a cargar las visitas.']);
            }
            if (! $plan->interval_months && $plan->schedule_precision === 'month') {
                throw ValidationException::withMessages(['planning' => 'Plan histórico pendiente de revisión. Crea otro plan y confirma su frecuencia.']);
            }
            $start = (int) $plan->visits()->max('sequence');
            $created = 0;
            $committed = $plan->expected_visits;
            $existing = $plan->visits()->where('status', '!=', 'cancelled')->count();
            for ($sequence = $start + 1; $sequence <= $start + $count; $sequence++) {
                if ($committed && $existing + $created >= $committed) {
                    break;
                }
                $date = Schedule::date($plan, $sequence);
                $month = $date->format('Y-m');
                if ($date->year > 2200 || $date->year < 1900) {
                    throw ValidationException::withMessages(['planning' => 'El período queda fuera del calendario admitido.']);
                }
                if ($plan->end_date && $date->gt($plan->end_date)) {
                    break;
                }
                $same = $plan->schedule_precision === 'month'
                    ? $plan->visits()->where('scheduled_month', $month)
                    : $plan->visits()->whereDate('scheduled_for', $date->format('Y-m-d'));
                if ($same->exists()) {
                    throw ValidationException::withMessages(['planning' => 'Ya existe una visita en ese período. Revisa las reprogramaciones.']);
                }
                $plan->visits()->create(['sequence' => $sequence, 'scheduled_month' => $month, 'scheduled_for' => $date->format('Y-m-d'),
                    'schedule_precision' => $plan->schedule_precision, 'technician_id' => $plan->technician_id]);
                $created++;
            }
            if (! $created) {
                throw ValidationException::withMessages(['planning' => 'No quedan períodos o visitas comprometidas por programar.']);
            }
            $plan->increment('schedule_version');

            return $created;
        });
    }
}
