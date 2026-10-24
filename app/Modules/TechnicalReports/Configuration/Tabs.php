<?php
namespace App\Modules\TechnicalReports\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return [
            'general' => ['label' => 'General', 'icon' => 'info', 'sections' => [
                ['title' => 'Informe del servicio', 'fields' => ['service_id', 'title'], 'columns' => 1],
            ]],
            'template' => ['label' => 'Plantilla', 'icon' => 'panel', 'sections' => [
                ['title' => 'Formato del documento', 'fields' => ['report_template_id'], 'columns' => 1],
            ]],
            'review' => ['label' => 'Revisión', 'icon' => 'file-text', 'sections' => [], 'view' => 'technical-reports::review'],
            'pdf' => ['label' => 'PDF', 'icon' => 'eye', 'sections' => [], 'view' => 'technical-reports::pdf'],
        ];
    }
}
