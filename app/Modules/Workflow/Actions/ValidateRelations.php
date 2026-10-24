<?php

namespace App\Modules\Workflow\Actions;

use App\Modules\Workflow\Configuration\FormFields;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ValidateRelations
{
    public function execute(string $module, array $data, ?Model $old): array
    {
        $relations = ['equipment' => ['client_id' => 'clients'], 'tickets' => ['equipment_id' => 'equipment'],
            'services' => ['ticket_id' => 'tickets'], 'activities' => ['service_id' => 'services'],
            'technical_reports' => ['service_id' => 'services']];
        foreach ($relations[$module] ?? [] as $field => $parent) {
            VisibleRecords::query($parent)->findOrFail($data[$field]);
        }
        if ($module === 'equipment' && ! $old && ! VisibleRecords::query('clients')->findOrFail($data['client_id'])->active) {
            throw ValidationException::withMessages(['client_id' => 'El cliente está inactivo.']);
        }
        if ($module === 'tickets' && (! $old || (int) $old->technician_id !== (int) $data['technician_id']) && ! array_key_exists($data['technician_id'], FormFields::technicians())) {
            throw ValidationException::withMessages(['technician_id' => 'Selecciona un técnico activo autorizado.']);
        }
        if ($module === 'tickets') { $data = app(\App\Modules\Tickets\Actions\TicketOriginValues::class)->execute($data, $old); }
        if ($module === 'equipment') { $data = app(\App\Modules\Catalogs\Actions\EquipmentCatalogValues::class)->execute($data, $old); }
        $this->protectHistory($module, $data, $old);
        if ($module === 'services') {
            $ticket = VisibleRecords::query('tickets')->findOrFail($data['ticket_id']);
            $data['technician_id'] = $old && (int) $old->ticket_id === (int) $ticket->id ? $old->technician_id : $ticket->technician_id;
        }

        return $data;
    }

    private function protectHistory(string $module, array $data, ?Model $old): void
    {
        if (! $old) { return; }
        $protected = match ($module) {
            'equipment' => ($old->tickets()->exists() || \App\Modules\MaintenancePlans\Models\MaintenancePlan::where('equipment_id', $old->id)->exists()) ? ['client_id', 'institution_id'] : [],
            'tickets' => $old->services()->exists() ? ['equipment_id', 'technician_id'] : [],
            'activities' => $old->protocol_application_id ? ['service_id'] : [],
            'services' => ($old->activities()->exists() || $old->reports()->exists()) ? ['ticket_id'] : [],
            default => [],
        };
        foreach ($protected as $field) {
            if ((string) $old->$field !== (string) ($data[$field] ?? '')) {
                throw ValidationException::withMessages([$field => 'Esta relación ya tiene historial y no puede cambiarse en el borrador actual.']);
            }
        }
    }
}
