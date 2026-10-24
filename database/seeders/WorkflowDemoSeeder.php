<?php

namespace Database\Seeders;

use App\Models\{Institution, User};
use App\Modules\Workflow\Configuration\Modules;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\TechnicalReports\Actions\CaptureReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('testing')) { $this->command?->warn('La carga de demostraciones está deshabilitada.'); return; }

        $user = User::where('is_active', true)->whereHas('roles', fn ($q) => $q->whereIn('name', ['Técnico', 'Engineer']))->orderBy('id')->first()
            ?? User::where('is_active', true)->whereHas('roles', fn ($q) => $q->whereIn('name', ['SuperAdmin', 'Administrador', 'Admin']))->orderBy('id')->first();
        if (! $user) { $this->command?->warn('DEMO pendiente: se necesita un técnico o administrador activo. No se crearon cuentas.'); return; }
        DB::transaction(function () use ($user) {
            $template = ReportTemplate::firstOrCreate(['name' => 'DEMO · Informe técnico'], ['definition' => Blocks::defaults(), 'description' => 'Plantilla de demostración con datos relacionados.']);
            foreach ($this->scenarios() as $index => $scenario) { $this->scenario($index + 1, $scenario, $user, $template); }
        });
        $this->command?->info('DEMO disponible: 2 clientes, 2 instituciones, 3 equipos, 3 tickets, 3 servicios, 6 actividades y 3 informes. Técnico: '.$user->name);
    }

    private function scenarios(): array
    {
        return [
            ['client' => 1, 'name' => 'Red Clínica del Valle', 'institution' => 'Clínica del Valle', 'city' => 'Quito', 'equipment' => 'Monitor multiparámetro', 'brand' => 'Mindray', 'model' => 'BeneVision N12', 'area' => 'Unidad de cuidados intensivos', 'type' => 'Mantenimiento preventivo', 'status' => 'completed', 'ticket' => 'resolved', 'summary' => 'Inspección, limpieza y verificación funcional completadas.', 'result' => 'Equipo operativo al finalizar la revisión.'],
            ['client' => 1, 'name' => 'Red Clínica del Valle', 'institution' => 'Clínica del Valle', 'city' => 'Quito', 'equipment' => 'Bomba de infusión', 'brand' => 'B. Braun', 'model' => 'Infusomat Space', 'area' => 'Hospitalización', 'type' => 'Mantenimiento correctivo', 'status' => 'in_progress', 'ticket' => 'in_progress', 'summary' => 'Revisión de alarma intermitente y cable de alimentación.', 'result' => 'Pendiente completar pruebas funcionales.'],
            ['client' => 2, 'name' => 'Centro Médico Horizonte', 'institution' => 'Centro Médico Horizonte', 'city' => 'Guayaquil', 'equipment' => 'Electrocardiógrafo', 'brand' => 'GE HealthCare', 'model' => 'MAC 2000', 'area' => 'Consulta externa', 'type' => 'Inspección técnica', 'status' => 'draft', 'ticket' => 'open', 'summary' => 'Evaluación inicial registrada para preparar la intervención.', 'result' => 'Hallazgos iniciales documentados.'],
        ];
    }

    private function scenario(int $number, array $s, User $user, ReportTemplate $template): void
    {
        $client = $this->record('clients', 'DEMO-CLI-'.$s['client'], ['name' => 'DEMO · '.$s['name'], 'active' => true, 'tax_id' => 'DEMO-ID-00'.$s['client'], 'address' => 'Avenida de demostración 100', 'phone' => '000-000-000'.$s['client'], 'city' => $s['city'], 'email' => 'contacto'.$s['client'].'@example.com'], $user);
        $institution = Institution::firstOrCreate(['code' => 'DEMO-INS-'.$s['client']], ['name' => 'DEMO · '.$s['institution'], 'city' => $s['city'], 'contact_email' => 'centro'.$s['client'].'@example.com', 'contact_phone' => '000-000-001'.$s['client'], 'type' => 'clinic']);
        $equipment = $this->record('equipment', 'DEMO-EQ-'.$number, ['client_id' => $client->id, 'institution_id' => $institution->id, 'name' => 'DEMO · '.$s['equipment'], 'brand' => $s['brand'], 'model' => $s['model'], 'serial' => 'DEMO-SERIE-00'.$number, 'location' => $s['area'], 'status' => $s['status'] === 'in_progress' ? 'maintenance' : 'operational'], $user);
        $ticket = $this->record('tickets', 'DEMO-TK-'.$number, ['origin' => 'internal', 'equipment_id' => $equipment->id, 'technician_id' => $user->id, 'subject' => 'DEMO · '.$s['type'], 'description' => 'Caso ficticio para revisar el flujo de trabajo y su diseño.', 'priority' => $number === 2 ? 'high' : 'normal', 'status' => $s['ticket'], 'opened_at' => now()->subDays(4 - $number)], $user);
        $service = $this->record('services', 'DEMO-SV-'.$number, ['ticket_id' => $ticket->id, 'technician_id' => $ticket->technician_id, 'service_date' => today()->subDays(3 - $number), 'service_type' => $s['type'], 'status' => $s['status'], 'summary' => $s['summary'], 'recommendations' => "Registrar la próxima revisión en el sistema.\nConservar el historial del equipo."], $user);
        foreach (['Inspección visual del equipo y accesorios.', 'Revisión funcional y documentación de hallazgos.'] as $i => $description) {
            $this->record('activities', 'DEMO-AC-'.$number.'-'.($i + 1), ['service_id' => $service->id, 'description' => $description, 'result' => $s['result'], 'photos' => []], $user);
        }
        app(\App\Modules\Catalogs\Actions\BackfillEquipmentCatalogs::class)->execute();
        $report = $this->record('technical_reports', 'DEMO-IT-'.$number, ['service_id' => $service->id, 'report_template_id' => $template->id, 'title' => 'DEMO · Informe de '.$s['equipment'], 'snapshot' => [], 'revision' => 1], $user);
        if (! $report->snapshot) { app(CaptureReport::class)->execute($report); }
    }

    private function record(string $module, string $code, array $values, User $user)
    {
        return Modules::get($module)['model']::firstOrCreate(['code' => $code], [...$values, 'lock_version' => 1, 'created_by' => $user->id]);
    }
}
