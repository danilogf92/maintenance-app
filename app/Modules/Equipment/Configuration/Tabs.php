<?php

namespace App\Modules\Equipment\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'cpu', 'sections' => [
            ['title' => 'Datos del equipo', 'fields' => ['name', 'brand_id', 'equipment_model_id', 'serial', 'status'], 'widths' => ['name' => 'full']]]],
            'location' => ['label' => 'Ubicación', 'icon' => 'map-pin', 'sections' => [
                ['title' => 'Propiedad y ubicación', 'fields' => ['client_id', 'institution_id', 'location'], 'columns' => 1]]],
            'received' => ['label'=>'Detalles','icon'=>'file-text','sections'=>[],'view'=>'equipment::received'],
            'planning' => ['label' => 'Planificación', 'icon' => 'calendar', 'relation' => 'maintenance_plans'],
            'tickets' => ['label' => 'Tickets', 'icon' => 'ticket', 'relation' => 'tickets']];
    }
}
