<?php

namespace App\Modules\Contracts\Actions;

use App\Modules\Contracts\Configuration\Permissions;
use App\Modules\Contracts\Queries\ContractQuery;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveCoverage
{
    public function execute(int $contractId, array $input, ?int $id = null)
    {
        Permissions::authorize('manage');
        $data = Validator::make($input, ['equipment_id' => 'required|integer|exists:equipment,id',
            'first_month' => 'required|date_format:Y-m|after_or_equal:1900-01|before:2201-01',
            'end_month' => 'required|date_format:Y-m|after_or_equal:first_month|before:2201-01',
            'requirements' => 'nullable|string|max:5000', 'lock_version' => 'required|integer|min:0'])->validate();

        return DB::transaction(function () use ($contractId, $data, $id) {
            $contract = ContractQuery::scoped()->lockForUpdate()->findOrFail($contractId);
            if (! $contract->active) {
                throw ValidationException::withMessages(['coverage' => 'Activa el contrato antes de modificar su cobertura.']);
            }
            $old = $id ? $contract->coverages()->lockForUpdate()->findOrFail($id) : null;
            if ($old && $old->lock_version !== (int) $data['lock_version']) {
                throw ValidationException::withMessages(['coverage' => 'La cobertura cambió. Vuelve a abrirla.']);
            }
            $equipment = VisibleRecords::query('equipment')->findOrFail($data['equipment_id']);
            if ($contract->client_id && $equipment->client_id && $contract->client_id !== $equipment->client_id) {
                throw ValidationException::withMessages(['equipment_id' => 'El equipo pertenece a otro cliente. Revisa la relación antes de vincularlo.']);
            }
            if ($contract->coverages()->where('equipment_id', $equipment->id)->when($id, fn ($q) => $q->whereKeyNot($id))->exists()) {
                throw ValidationException::withMessages(['equipment_id' => 'Este equipo ya tiene cobertura en el contrato.']);
            }
            if ($old && $old->plans()->exists()) {
                foreach (['equipment_id', 'first_month', 'end_month'] as $key) {
                    if ((string) $old->$key !== (string) $data[$key]) {
                        throw ValidationException::withMessages([$key => 'La cobertura ya tiene planes. Se conserva su vigencia y equipo.']);
                    }
                }
            }
            $record = $old ?? $contract->coverages()->make();
            $record->fill($data);
            $record->lock_version = ($old?->lock_version ?? 0) + 1;
            $record->save();

            return $record->fresh();
        });
    }
}
