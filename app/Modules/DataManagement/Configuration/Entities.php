<?php
namespace App\Modules\DataManagement\Configuration;
class Entities
{
    public static function all(): array
    {
        return ['clients' => 'Clientes', 'institutions' => 'Instituciones', 'contacts' => 'Contactos',
            'contact_links' => 'Vínculos de contactos', 'manufacturers' => 'Fabricantes', 'brands' => 'Marcas',
            'equipment_models' => 'Modelos', 'equipment' => 'Equipos', 'field_engineers' => 'Ingenieros de servicio',
            'equipment_client_histories' => 'Historial de clientes de equipos', 'equipment_institution_histories' => 'Historial de centros de equipos',
            'tickets' => 'Tickets', 'ticket_field_engineer' => 'Asignaciones históricas de ingenieros',
            'services' => 'Servicios', 'activities' => 'Actividades y hallazgos', 'technical_reports' => 'Informes técnicos',
            'maintenance_contracts' => 'Contratos', 'contract_equipment_coverages' => 'Coberturas de equipos por contrato',
            'maintenance_plans' => 'Planes de mantenimiento', 'maintenance_visits' => 'Visitas planificadas',
            'maintenance_protocols' => 'Protocolos', 'maintenance_protocol_steps' => 'Pasos de protocolos',
            'maintenance_protocol_files' => 'Referencias de protocolos', 'service_protocol_applications' => 'Protocolos aplicados'];
    }
    public static function label(string $table): string { return self::all()[$table] ?? ($table === 'users' ? 'Usuarios del sistema' : $table); }
}
