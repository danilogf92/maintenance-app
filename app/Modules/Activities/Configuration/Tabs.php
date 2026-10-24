<?php

namespace App\Modules\Activities\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return [
            'general' => ['label' => 'General', 'icon' => 'info', 'sections' => [
                ['title' => 'Servicio relacionado', 'fields' => ['service_id'], 'columns' => 1],
            ]],
            'work' => ['label' => 'Actividad y resultado', 'icon' => 'activity', 'sections' => [
                ['title' => 'Registro del trabajo', 'fields' => ['description', 'result'], 'columns' => 1],
            ]],
            'protocol' => ['label' => 'Protocolo', 'icon' => 'activity', 'sections' => [], 'view' => 'maintenance-protocols::activity'],
            'evidence' => ['label' => 'Evidencias', 'icon' => 'image', 'sections' => [
                ['title' => 'Fotografías de evidencia', 'fields' => ['photos'], 'columns' => 1],
            ]],
        ];
    }
}
