<?php

namespace App\Modules\Contracts\Actions;

use App\Modules\Contracts\Configuration\Permissions;
use App\Modules\Contracts\Models\MaintenanceContract;
use App\Modules\Contracts\Queries\ContractQuery;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveContract
{
    public function execute(array $input, ?int $id = null): MaintenanceContract
    {
        Permissions::authorize('manage');
        $input['client_id'] = ($input['client_id'] ?? '') ?: null;
        $input['number'] = trim($input['number'] ?? '');
        $input['name'] = trim($input['name'] ?? '');
        $data = Validator::make($input, ['number' => ['required', 'string', 'max:100', Rule::unique('maintenance_contracts')->ignore($id)],
            'name' => 'required|string|max:160', 'client_id' => 'nullable|integer|exists:clients,id', 'active' => 'required|boolean',
            'notes' => 'nullable|string|max:5000', 'lock_version' => 'required|integer|min:0'])->validate();

        return DB::transaction(function () use ($data, $id) {
            $old = $id ? ContractQuery::scoped()->lockForUpdate()->findOrFail($id) : null;
            if ($old && $old->lock_version !== (int) $data['lock_version']) {
                throw ValidationException::withMessages(['lock_version' => 'El contrato cambió. Recarga antes de guardar.']);
            }
            if ($data['client_id']) {
                VisibleRecords::query('clients')->findOrFail($data['client_id']);
            }
            if ($old && $old->client_id && $old->client_id != $data['client_id'] && $old->coverages()->exists()) {
                throw ValidationException::withMessages(['client_id' => 'El contrato ya tiene equipos. Se conserva el cliente de su historial.']);
            }
            if ($old && ! $old->client_id && $data['client_id'] && $old->coverages()->whereHas('equipment',
                fn ($q) => $q->whereNotNull('client_id')->where('client_id', '!=', $data['client_id']))->exists()) {
                throw ValidationException::withMessages(['client_id' => 'Un equipo del contrato pertenece a otro cliente. Revisa sus relaciones.']);
            }
            $record = $old ?? new MaintenanceContract(['code' => 'CT-'.Str::upper(Str::random(8)), 'created_by' => auth()->id()]);
            $record->fill($data);
            $record->lock_version = ($old?->lock_version ?? 0) + 1;
            $record->save();

            return $record->fresh();
        });
    }
}
