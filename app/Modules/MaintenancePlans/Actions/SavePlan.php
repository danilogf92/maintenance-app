<?php

namespace App\Modules\MaintenancePlans\Actions;

use App\Modules\Contracts\Models\EquipmentCoverage;
use App\Modules\Contracts\Queries\ContractQuery;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Configuration\Rules;
use App\Modules\MaintenancePlans\Configuration\Schedule;
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\Workflow\Queries\VisibleRecords;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SavePlan
{
    public function execute(array $input, ?int $id = null): MaintenancePlan
    {
        Permissions::authorize('manage');
        foreach (['technician_id', 'end_month', 'contract_coverage_id', 'expected_visits'] as $key) {
            $input[$key] = ($input[$key] ?? '') !== '' ? $input[$key] : null;
        }
        $input['interval_unit'] ??= 'months';
        $input['interval_value'] ??= $input['interval_months'] ?? null;
        $input['schedule_precision'] = in_array($input['interval_unit'], ['days', 'weeks']) ? 'day' : 'month';
        if ($input['schedule_precision'] === 'day') {
            $input['first_month'] = substr($input['first_date'] ?? '', 0, 7);
            $input['end_month'] = ($input['end_date'] ?? '') ? substr($input['end_date'], 0, 7) : null;
        }
        if ($input['schedule_precision'] === 'month') {
            unset($input['first_date'],$input['end_date']);
        }
        $data = Validator::make($input, Rules::make())->validate();
        $data['name'] = trim($data['name']);
        if ($data['name'] === '') {
            throw ValidationException::withMessages(['name' => 'Escribe el nombre del plan.']);
        }
        if ($data['schedule_precision'] === 'month') {
            $data['first_date'] = $data['first_month'].'-01';
            $data['end_date'] = $data['end_month'] ? CarbonImmutable::parse($data['end_month'].'-01')->endOfMonth()->format('Y-m-d') : null;
        } else {
            $data['end_date'] = ($data['end_date'] ?? '') ?: null;
        }
        $data['interval_months'] = match ($data['interval_unit']) {
            'months' => (int) $data['interval_value'], 'years' => (int) $data['interval_value'] * 12, default => null
        };

        return DB::transaction(function () use ($data, $id) {
            $old = $id ? PlanQuery::scoped()->lockForUpdate()->findOrFail($id) : null;
            if ($old && $old->lock_version !== (int) $data['lock_version']) {
                throw ValidationException::withMessages(['lock_version' => 'El plan cambió. Recarga antes de guardar.']);
            }
            VisibleRecords::query('equipment')->findOrFail($data['equipment_id']);
            VisitRules::technician($data['technician_id'] ?? null, $old?->technician_id);
            if ($data['contract_coverage_id']) {
                $coverage = EquipmentCoverage::whereHas('contract', fn ($q) => $q->whereIn('id', ContractQuery::scoped()->select('id')))
                    ->with('contract')->findOrFail($data['contract_coverage_id']);
                if (! $coverage->contract->active || $coverage->equipment_id !== (int) $data['equipment_id']) {
                    throw ValidationException::withMessages(['contract_coverage_id' => 'Selecciona la cobertura activa de este mismo equipo.']);
                }
                if (! $data['end_month'] || $data['first_month'] < $coverage->first_month || $data['end_month'] > $coverage->end_month) {
                    throw ValidationException::withMessages(['end_month' => 'La vigencia del plan debe quedar dentro de la cobertura del equipo en el contrato.']);
                }
            }
            if ($old && $old->visits()->exists()) {
                $keys = ['equipment_id', 'contract_coverage_id', 'first_month', 'end_month', 'interval_value', 'interval_unit', 'expected_visits', 'schedule_precision'];
                if ($old->schedule_precision === 'day') {
                    $keys = [...$keys, 'first_date', 'end_date'];
                }
                foreach ($keys as $key) {
                    $previous = $old->$key;
                    if ($previous instanceof \DateTimeInterface) {
                        $previous = $previous->format('Y-m-d');
                    }
                    if ((string) $previous !== (string) ($data[$key] ?? '')) {
                        throw ValidationException::withMessages([$key => 'El plan ya tiene visitas. Crea otro plan para cambiar equipo, contrato, vigencia o frecuencia.']);
                    }
                }
            }
            // Preserve historical date columns when editing a monthly plan without changing its periods.
            if ($old && $data['schedule_precision'] === 'month') {
                if ($old->first_month === $data['first_month']) {
                    $data['first_date'] = $old->first_date->format('Y-m-d');
                }
                if ($old->end_month === $data['end_month'] && $old->end_date) {
                    $data['end_date'] = $old->end_date->format('Y-m-d');
                }
            }
            $record = $old ?? new MaintenancePlan(['code' => 'PM-'.Str::upper(Str::random(8)), 'created_by' => auth()->id()]);
            $record->fill($data);
            $capacity = Schedule::capacity($record);
            if ($record->expected_visits && $capacity !== null && $record->expected_visits > $capacity) {
                throw ValidationException::withMessages(['expected_visits' => 'La cantidad comprometida supera las visitas posibles con esta frecuencia y vigencia.']);
            }
            $record->lock_version = ($old?->lock_version ?? 0) + 1;
            $record->save();

            return $record->fresh();
        });
    }
}
