<?php

namespace Tests\Support;

use App\Models\Institution;
use App\Modules\ReportTemplates\Rendering\ImageData;

/** In-memory renderer fixtures. Never used by the application or seeders. */
class ReportFixture
{
    public function execute(int $activityCount = 4, ?int $institutionId = null): array
    {
        $institution = $institutionId ? Institution::findOrFail($institutionId)->only(['name', 'city', 'contact_email', 'contact_phone']) :
            ['name' => 'Hospital de ejemplo', 'city' => 'Quito', 'contact_email' => 'ejemplo@example.com', 'contact_phone' => '0000000000'];
        $photo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('report-assets/demo-equipment.png')));
        $activities = [];
        for ($index = 1; $index <= $activityCount; $index++) {
            $activities[] = ['description' => "Actividad {$index}: inspección y verificación del equipo.",
                'result' => 'Completado · dato de ejemplo', 'photos' => [
                    ['src' => $photo, 'caption' => "Evidencia {$index} A (ejemplo)"],
                    ['src' => $photo, 'caption' => "Evidencia {$index} B (ejemplo)"],
                ]];
        }

        return ['is_sample' => true, 'report' => ['number' => 'PRUEBA', 'date' => now()->format('d/m/Y')],
            'ticket' => ['number' => 'EJEMPLO-2318'], 'service' => ['type' => 'Mantenimiento preventivo'],
            'institution' => $institution, 'client' => ['name' => 'Cliente de ejemplo', 'tax_id' => '0000000000000'],
            'equipment' => ['name' => 'Equipo de ejemplo', 'brand' => 'Livanova', 'model' => 'S5',
                'serial' => 'DEMO-0248', 'location' => 'Área de ejemplo', 'status' => 'Operativo'],
            'technician' => ['name' => auth()->user()?->name ?? 'Técnico de ejemplo'],
            'activities' => $activities, 'components' => [['description' => 'Componente de ejemplo', 'result' => 'Operativo', 'photos' => []]],
            'parts' => [['description' => 'Repuesto de ejemplo', 'code' => 'DEMO-01', 'quantity' => 1, 'status' => 'Instalado']],
            'recommendations' => ['Mantener el cronograma preventivo.', 'Verificar el estado de cables y conectores.']];
    }
}
