<?php
namespace App\Modules\MaintenanceProtocols\Actions;
use App\Modules\Activities\Models\Activity;
use App\Modules\MaintenanceProtocols\Configuration\Permissions;
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolApplication};
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ApplyProtocol
{
    public function execute(int $serviceId, int $protocolId): ProtocolApplication
    {
        Permissions::authorize('apply'); Permissions::authorize();
        WorkflowPermissions::authorize('services', 'manage'); WorkflowPermissions::authorize('activities', 'manage');
        return DB::transaction(function () use ($serviceId, $protocolId) {
            $service = VisibleRecords::query('services')->lockForUpdate()->findOrFail($serviceId);
            if ($service->ninoxImport()->exists() || $service->ticket?->ninoxImport()->exists()) {
                $this->invalid('El servicio histórico está en consulta; primero deben verificarse sus relaciones.');
            }
            $existing = ProtocolApplication::where('service_id', $service->id)->where('maintenance_protocol_id', $protocolId)->first();
            if ($existing) { return $existing; }
            if (in_array($service->status, ['completed', 'cancelled'], true)) { $this->invalid('El servicio está cerrado o cancelado.'); }
            $protocol = MaintenanceProtocol::lockForUpdate()->findOrFail($protocolId);
            if ($protocol->is_obsolete !== false) { $this->invalid('El protocolo es obsoleto o su vigencia está pendiente de verificar.'); }
            $steps = $protocol->steps()->lockForUpdate()->get();
            if ($steps->isEmpty()) { $this->invalid('El protocolo no contiene pasos confirmados.'); }
            foreach ($steps as $step) {
                if (trim($step->instruction ?? '') === '' && trim($step->report_text ?? '') === '') {
                    $this->invalid('Faltan instrucciones y texto en el paso de origen '.$step->source_id.'. Corrige el origen antes de aplicarlo.');
                }
            }
            $application = ProtocolApplication::create(['service_id' => $service->id, 'maintenance_protocol_id' => $protocol->id,
                'created_by' => auth()->id(), 'snapshot' => ['code' => $protocol->code, 'name' => $protocol->name,
                    'version' => $protocol->version, 'source_hash' => $protocol->source_hash, 'step_count' => $steps->count()]]);
            foreach ($steps as $step) {
                $description = trim($step->report_text ?? '') ?: trim($step->instruction ?? '');
                if (mb_strlen($description) > 5000) { $description = 'Paso '.$step->step_number.' · Consulta las instrucciones completas en la pestaña Protocolo.'; }
                Activity::create(['code' => 'AC-'.Str::upper(Str::random(12)), 'service_id' => $service->id,
                    'description' => $description, 'result' => null, 'photos' => [], 'lock_version' => 1, 'created_by' => auth()->id(),
                    'protocol_application_id' => $application->id, 'protocol_step_id' => $step->id,
                    'protocol_include_in_report' => $step->include_in_report,
                    'protocol_snapshot' => ['protocol_name' => $protocol->name, 'protocol_code' => $protocol->code,
                        'version' => $protocol->version, 'step_number' => $step->step_number, 'position' => $step->position,
                        'instruction' => $step->instruction, 'report_text' => $step->report_text, 'range' => $step->range,
                        'type' => $step->type, 'include_in_report' => $step->include_in_report]]);
            }
            return $application;
        });
    }
    private function invalid(string $message): never { throw ValidationException::withMessages(['protocolId' => $message]); }
}
