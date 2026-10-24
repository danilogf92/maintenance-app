<?php

namespace App\Modules\ReportTemplates\Configuration;

class Variables
{
    public static function options(): array
    {
        return [
            'report.title' => 'Informe → Título', 'service.number' => 'Servicio → Código', 'service.date' => 'Servicio → Fecha',
            'service.summary' => 'Servicio → Resumen', 'equipment.code' => 'Equipo → Código',
            'client.address' => 'Cliente → Dirección', 'client.email' => 'Cliente → Correo', 'client.phone' => 'Cliente → Teléfono',
            'report.number' => 'Informe → Número', 'report.date' => 'Informe → Fecha',
            'ticket.number' => 'Ticket → Número', 'service.type' => 'Servicio → Tipo',
            'institution.name' => 'Institución → Nombre', 'institution.city' => 'Institución → Ciudad',
            'institution.contact_email' => 'Institución → Correo',
            'institution.contact_phone' => 'Institución → Teléfono',
            'client.name' => 'Cliente → Nombre', 'client.tax_id' => 'Cliente → RUC',
            'equipment.name' => 'Equipo → Nombre', 'equipment.brand' => 'Equipo → Marca',
            'equipment.manufacturer' => 'Equipo → Fabricante', 'equipment.model' => 'Equipo → Modelo', 'equipment.serial' => 'Equipo → Serie',
            'equipment.location' => 'Equipo → Ubicación', 'equipment.status' => 'Equipo → Estado',
            'technician.name' => 'Técnico → Nombre',
        ];
    }

    public static function value(array $data, string $key): string
    {
        return array_key_exists($key, self::options()) ? (string) data_get($data, $key, '—') : '—';
    }
}
