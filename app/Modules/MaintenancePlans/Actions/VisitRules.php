<?php
namespace App\Modules\MaintenancePlans\Actions;
use App\Models\User;
use App\Modules\Workflow\Configuration\FormFields;
use Illuminate\Validation\ValidationException;
class VisitRules
{
    public static function technician(?int $id, ?int $previous = null): void
    {
        if (!$id || $id === $previous) { return; }
        if (!array_key_exists($id, FormFields::technicians()) || !User::whereKey($id)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['technician_id' => 'Selecciona un técnico activo autorizado.']);
        }
    }
    public static function active($plan): void
    {
        if (!$plan->active || ($plan->contract_coverage_id && !$plan->coverage?->contract?->active)) { throw ValidationException::withMessages(['planning' => 'Activa el plan y su contrato antes de programar visitas o generar tickets.']); }
    }
}
