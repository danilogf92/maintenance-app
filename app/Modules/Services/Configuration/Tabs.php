<?php

namespace App\Modules\Services\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return [
            'general' => ['label' => 'General', 'icon' => 'info', 'view' => 'services::history-notice', 'sections' => [
                ['title' => 'Datos del servicio', 'fields' => ['ticket_id', 'service_date', 'status'], 'widths' => ['ticket_id' => 'full']],
            ]],
            'intervention' => ['label' => 'Intervención', 'icon' => 'wrench', 'sections' => [
                ['title' => 'Trabajo realizado', 'fields' => ['service_type', 'summary', 'recommendations'], 'columns' => 1],
            ]],
            'ninox' => ['label' => 'Detalles', 'icon' => 'file-text', 'sections' => [], 'view' => 'services::ninox'],
            'protocols' => ['label' => 'Protocolos', 'icon' => 'activity', 'relation' => 'maintenance_protocols'],
            'activities' => ['label' => 'Actividades', 'icon' => 'activity', 'relation' => 'activities'],
            'reports' => ['label' => 'Informes', 'icon' => 'file-text', 'relation' => 'technical_reports'],
        ];
    }
}
