<?php

namespace App\Modules\MaintenancePlans\Actions;

use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveVisit
{
    public function execute(int $planId, int $visitId, array $input, int $version)
    {
        Permissions::authorize('manage');
        $input['technician_id'] = ($input['technician_id'] ?? '') !== '' ? $input['technician_id'] : null;

        return DB::transaction(function () use ($planId, $visitId, $input, $version) {
            $plan = PlanQuery::scoped()->lockForUpdate()->findOrFail($planId);
            VisitRules::active($plan);
            $visit = $plan->visits()->lockForUpdate()->findOrFail($visitId);
            $daily = $visit->schedule_precision === 'day';
            $rules = ['technician_id' => 'nullable|integer|exists:users,id', 'notes' => 'nullable|string|max:5000', 'status' => 'required|in:planned,cancelled'];
            $key = $daily ? 'scheduled_for' : 'scheduled_month';
            $rules[$key] = $daily ? 'required|date_format:Y-m-d' : 'required|date_format:Y-m';
            $data = Validator::make($input, $rules)->validate();
            if ($visit->lock_version !== $version) {
                throw ValidationException::withMessages(['planning' => 'La visita cambió. Vuelve a abrirla.']);
            }
            if ($visit->ticket()->exists()) {
                throw ValidationException::withMessages(['planning' => 'La visita ya tiene ticket. Gestiona la atención desde ese ticket.']);
            }
            $date = CarbonImmutable::parse($data[$key].($daily ? '' : '-01'));
            $outside = $daily ? $date->lt($plan->first_date) || ($plan->end_date && $date->gt($plan->end_date))
                : $data[$key] < $plan->first_month || ($plan->end_month && $data[$key] > $plan->end_month);
            if ($outside) {
                throw ValidationException::withMessages([$key => 'El período debe estar dentro de la vigencia del plan.']);
            }
            $duplicates = $plan->visits()->whereKeyNot($visit->id);
            $duplicates = $daily ? $duplicates->whereDate($key, $data[$key]) : $duplicates->where($key, $data[$key]);
            if ((string) ($daily ? $visit->scheduled_for->format('Y-m-d') : $visit->scheduled_month) !== $data[$key]
                && $duplicates->exists()) {
                throw ValidationException::withMessages([$key => 'El plan ya tiene una visita en ese período.']);
            }
            if ($visit->status === 'cancelled' && $data['status'] === 'planned' && $plan->expected_visits
                && $plan->visits()->where('status', '!=', 'cancelled')->count() >= $plan->expected_visits) {
                throw ValidationException::withMessages(['planning' => 'Reactivar superaría la cantidad comprometida del plan.']);
            }
            $data['scheduled_month'] = $date->format('Y-m');
            if ($daily || $visit->scheduled_month !== $data['scheduled_month']) {
                $data['scheduled_for'] = $date->format('Y-m-d');
            }
            VisitRules::technician($data['technician_id'] ?? null, $visit->technician_id);
            $visit->fill($data);
            $visit->lock_version++;
            $visit->save();

            return $visit->fresh();
        });
    }
}
