<?php
namespace App\Modules\MaintenanceProtocols\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'info', 'sections' => [
            ['title' => 'Protocolo de mantenimiento', 'fields' => ['name', 'code', 'version', 'type', 'status_label', 'model_info', 'description'],
                'widths' => ['name' => 'full', 'description' => 'full', 'model_info' => 'full']]]],
            'steps' => ['label' => 'Pasos e instrucciones', 'icon' => 'activity', 'sections' => [], 'view' => 'maintenance-protocols::steps'],
            'relations' => ['label' => 'Relaciones', 'icon' => 'cpu', 'sections' => [], 'view' => 'maintenance-protocols::relations']];
    }
}
