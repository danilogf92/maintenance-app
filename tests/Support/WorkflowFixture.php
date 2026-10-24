<?php

namespace Tests\Support;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Workflow\Actions\SaveRecord;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

trait WorkflowFixture
{
    protected User $admin;

    protected function bootWorkflow(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::factory()->create(['can_access_admin' => true]);
        $this->admin->assignRole('SuperAdmin'); $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function saveWorkflow(string $module, array $data, ?int $id = null)
    {
        return app(SaveRecord::class)->execute($module, ['lock_version' => 0, ...$data], $id);
    }

    protected function workflow(?User $technician = null): array
    {
        $client = $this->saveWorkflow('clients', ['name' => 'Cliente real de prueba', 'tax_id' => '1791234567001', 'active' => true]);
        $institution = Institution::factory()->create(['name' => 'Hospital de prueba']);
        $equipment = $this->saveWorkflow('equipment', ['client_id' => $client->id, 'institution_id' => $institution->id,
            'name' => 'Monitor técnico', 'serial' => 'SERIE-IGUAL', 'brand' => 'Marca A', 'model' => 'Modelo B', 'status' => 'operational']);
        $ticket = $this->saveWorkflow('tickets', ['equipment_id' => $equipment->id, 'technician_id' => $technician?->id ?? $this->admin->id,
            'subject' => 'Mantenimiento solicitado', 'priority' => 'normal', 'status' => 'open', 'opened_at' => now()->toDateTimeString()]);
        $service = $this->saveWorkflow('services', ['ticket_id' => $ticket->id, 'service_date' => '2026-10-16',
            'service_type' => 'Preventivo', 'status' => 'in_progress', 'summary' => 'Inspección real', 'recommendations' => "Revisar cables\nPróxima visita"]);
        $activity = $this->saveWorkflow('activities', ['service_id' => $service->id, 'description' => 'Verificación eléctrica real', 'result' => 'Conforme', 'photos' => []]);

        return compact('client', 'institution', 'equipment', 'ticket', 'service', 'activity');
    }

    protected function evidence(string $name = 'evidence.png'): string
    {
        $path = 'service-photos/'.auth()->id().'/'.$name;
        Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aP9sAAAAASUVORK5CYII='));

        return $path;
    }
}
