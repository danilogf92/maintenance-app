<?php

namespace App\Modules\Tickets\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'view' => 'tickets::history-notice', 'icon' => 'ticket', 'sections' => [
            ['title' => 'Datos del ticket', 'fields' => ['subject', 'description', 'priority', 'status', 'opened_at'],
                'widths' => ['subject' => 'full', 'description' => 'full', 'opened_at' => 'full']]]],
            'assignment' => ['label' => 'Asignación', 'icon' => 'user', 'sections' => [
                ['title' => 'Equipo y responsable', 'fields' => ['equipment_id', 'technician_id'], 'columns' => 1]]],
            'origin' => ['label' => 'Origen', 'icon' => 'info', 'sections' => [
                ['title' => 'Motivo y solicitante', 'fields' => ['origin', 'requester_contact_id', 'reported_by'], 'columns' => 1]], 'view' => 'tickets::planning-context'],
            'ninox' => ['label'=>'Detalles','icon'=>'file-text','sections'=>[],'view'=>'tickets::ninox'],
            'services' => ['label' => 'Servicios', 'icon' => 'wrench', 'relation' => 'services']];
    }
}
