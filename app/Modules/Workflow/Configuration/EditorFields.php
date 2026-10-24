<?php

namespace App\Modules\Workflow\Configuration;

class EditorFields
{
    public static function make(string $module): array
    {
        return match ($module) {
            'clients' => ['name' => ['Nombre / razón social'], 'tax_id' => ['RUC / identificación'], 'email' => ['Correo', 'email'],
                'phone' => ['Teléfono'], 'address' => ['Dirección'], 'city' => ['Ciudad'], 'active' => ['Activo', 'checkbox']],
            'equipment' => ['client_id' => ['Cliente propietario', 'clients'], 'institution_id' => ['Institución', 'institutions'],
                'name' => ['Equipo'], 'brand_id' => ['Marca', 'brands'], 'equipment_model_id' => ['Modelo', 'equipment_models'], 'serial' => ['Serie'], 'location' => ['Ubicación'], 'status' => ['Estado', 'status']],
            'tickets' => ['equipment_id' => ['Equipo', 'equipment'], 'technician_id' => ['Técnico', 'technicians'], 'subject' => ['Asunto'],
                'description' => ['Descripción', 'textarea'], 'priority' => ['Prioridad', 'priority'], 'status' => ['Estado', 'status'], 'opened_at' => ['Fecha de apertura', 'datetime-local']],
            'services' => ['ticket_id' => ['Ticket', 'tickets'], 'service_date' => ['Fecha de servicio', 'date'], 'service_type' => ['Tipo de servicio'],
                'status' => ['Estado', 'status'], 'summary' => ['Resumen', 'textarea'], 'recommendations' => ['Recomendaciones (una por línea)', 'textarea']],
            'activities' => ['service_id' => ['Servicio', 'services'], 'description' => ['Actividad / hallazgo', 'textarea'], 'result' => ['Resultado', 'textarea']],
            'technical_reports' => ['service_id' => ['Servicio', 'services'], 'report_template_id' => ['Plantilla PDF', 'templates'], 'title' => ['Título']],
        };
    }

    public static function defaults(string $module): array
    {
        $values = array_fill_keys(array_keys(self::make($module)), '');
        return [...$values, 'lock_version' => 0, ...match ($module) {
            'clients' => ['active' => true], 'equipment' => ['status' => 'operational'],
            'tickets' => ['technician_id' => auth()->id(), 'priority' => 'normal', 'status' => 'open', 'opened_at' => now()->format('Y-m-d\TH:i')],
            'services' => ['service_date' => today()->toDateString(), 'status' => 'draft', 'service_type' => 'Mantenimiento preventivo'],
            'activities' => ['photos' => []], 'technical_reports' => ['title' => 'Informe técnico'],
        }];
    }

    public static function options(string $type, string $module): array
    {
        if ($type === 'brands') { return \App\Modules\Catalogs\Queries\CatalogOptions::get('brands'); }
        if ($type === 'equipment_models') {
            return \App\Modules\EquipmentModels\Models\EquipmentModel::where('active', true)->orderBy('name')->limit(500)->pluck('name', 'id')->all();
        }
        if ($type === 'status') { return Modules::configuration($module, 'Fields')::statuses(); }
        if ($type === 'priority') { return ['low' => 'Baja', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente']; }
        if ($type === 'technicians') { return FormFields::technicians(); }
        if ($type === 'institutions') { return \App\Models\Institution::orderBy('name')->limit(500)->pluck('name', 'id')->all(); }
        if ($type === 'templates') { return \App\Modules\ReportTemplates\Models\ReportTemplate::orderBy('name')->pluck('name', 'id')->all(); }
        if (! in_array($type, ['clients', 'equipment', 'tickets', 'services'])) { return []; }
        return \App\Modules\Workflow\Queries\VisibleRecords::query($type)->latest('id')->limit(500)->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->code.' · '.($r->name ?? $r->subject ?? $r->service_type ?? '')])->all();
    }
}
