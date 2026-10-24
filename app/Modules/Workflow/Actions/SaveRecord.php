<?php

namespace App\Modules\Workflow\Actions;

use App\Modules\Activities\Actions\PrivatePhotos;
use App\Modules\TechnicalReports\Actions\CaptureReport;
use App\Modules\Workflow\Configuration\Modules;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveRecord
{
    public function execute(string $module, array $input, ?int $id = null): Model
    {
        Permissions::authorize($module, 'manage');
        if ($module === 'equipment' && $id && VisibleRecords::query('equipment')->findOrFail($id)->receivedSource()->exists()) {
            throw ValidationException::withMessages(['equipment_id'=>'Equipo recibido en consulta. Primero deben verificarse sus relaciones; los valores originales están en Detalles.']);
        }
        $rules = Modules::configuration($module, 'Rules')::make();
        $data = Validator::make($input, ['lock_version' => 'required|integer|min:0', ...$rules])->validate();

        $this->protectImported($module, $data, $id);
        return DB::transaction(function () use ($module, $data, $id) {
            $old = $id ? VisibleRecords::query($module)->lockForUpdate()->findOrFail($id) : null;
            if ($old && (int) $old->lock_version !== (int) $data['lock_version']) {
                throw ValidationException::withMessages(['lock_version' => 'Otro usuario modificó este registro. Recarga antes de guardar.']);
            }
            $values = app(ValidateRelations::class)->execute($module, $data, $old);
            if ($module === 'activities') { $values['photos'] = PrivatePhotos::validate($values['photos'] ?? [], $old?->photos ?? []); }
            $definition = Modules::get($module);
            $record = $old ?? new $definition['model'];
            if (! $old) { $record->code = $definition['prefix'].'-'.Str::upper(Str::random(8)); $record->created_by = auth()->id(); }
            if ($module === 'technical_reports' && ! $old) { $record->snapshot = []; }
            $record->fill($values); $record->lock_version = ($old?->lock_version ?? 0) + 1; $record->save();
            if ($module === 'technical_reports') { app(CaptureReport::class)->execute($record); }

            return $record->fresh();
        });
    }
    private function protectImported(string $module, array $input, ?int $id): void
    {
        if ($module === 'tickets' && $id && VisibleRecords::query('tickets')->findOrFail($id)->ninoxImport()->exists()) {
            throw ValidationException::withMessages(['ticket_id'=>'Ticket histórico en consulta. Sus datos originales están en Detalles.']);
        }
        if ($module === 'services' && isset($input['ticket_id']) && VisibleRecords::query('tickets')->findOrFail($input['ticket_id'])->ninoxImport()->exists()) {
            throw ValidationException::withMessages(['ticket_id'=>'El ticket histórico está en consulta; primero deben verificarse sus relaciones.']);
        }
        $serviceId = $module === 'services' ? $id : ($module === 'technical_reports' ? ($input['service_id'] ?? null) : null);
        if (!$serviceId) { return; }
        $service = VisibleRecords::query('services')->findOrFail($serviceId);
        if ($service->ninoxImport()->exists()) {
            throw ValidationException::withMessages(['service_id' => 'Bitácora histórica en modo de consulta. Abre sus archivos originales en Servicios → Detalles.']);
        }
    }

}
