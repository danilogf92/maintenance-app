<?php

namespace Tests\Feature;

use App\Modules\Clients\Models\Client;
use App\Modules\Workflow\UI\OperationsPage;
use App\Modules\Workflow\Configuration\Modules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class WorkflowUiTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;

    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    public function test_six_modules_use_normal_layout_and_no_workspace_panel_exists(): void
    {
        $registry = app(\Livewire\Mechanisms\ComponentRegistry::class);
        $this->assertSame('workflow-operations', $registry->getName(OperationsPage::class));
        $this->assertSame(OperationsPage::class, $registry->getClass('workflow-operations'));
        $this->workflow();
        foreach (array_keys(Modules::all()) as $module) {
            $this->get(route('operations.'.$module))->assertOk()->assertSee('Navegación operativa')->assertDontSee('/workspace/');
            $record = Modules::get($module)['model']::first();
            if ($record) { Livewire::test(OperationsPage::class, ['module' => $module])->call('edit', $record->id)->assertSet('editingId', $record->id); }
        }
        $this->get('/workspace')->assertNotFound();
        $this->get('/workspace/clients')->assertNotFound();
        $this->assertSame(['admin'], array_keys(\Filament\Facades\Filament::getPanels()));
    }

    public function test_normal_editor_creates_and_saves_repeated_edits(): void
    {
        $page = Livewire::test(OperationsPage::class, ['module' => 'clients'])->call('create')
            ->set('form.name', 'Cliente desde interfaz')->call('save')->assertHasNoErrors();
        $client = Client::where('name', 'Cliente desde interfaz')->firstOrFail();
        $page->set('form.name', 'Primera edición')->call('save')->assertHasNoErrors()
            ->set('form.name', 'Segunda edición')->call('save')->assertHasNoErrors();
        $this->assertSame('Segunda edición', $client->fresh()->name);
        $this->assertSame(3, $client->fresh()->lock_version);
    }

    public function test_read_only_user_can_consult_but_cannot_save(): void
    {
        $f = $this->workflow();
        $user = \App\Models\User::factory()->create(); $user->assignRole('CEO'); $this->actingAs($user);
        Livewire::test(OperationsPage::class, ['module' => 'clients'])->call('edit', $f['client']->id)
            ->set('form.name', 'No permitido')->call('save')->assertForbidden();
    }

    public function test_demo_data_has_real_foreign_keys_and_is_idempotent(): void
    {
        $this->seed(\Database\Seeders\WorkflowDemoSeeder::class);
        $report = \App\Modules\TechnicalReports\Models\TechnicalReport::where('code', 'DEMO-IT-1')->firstOrFail();
        $this->assertSame('DEMO-CLI-1', $report->service->ticket->equipment->client->code);
        $this->assertSame('DEMO-INS-1', $report->service->ticket->equipment->institution->code);
        $this->assertSame(2, $report->service->activities()->count());
        $this->assertSame($report->service->technician_id, $report->service->ticket->technician_id);
        $this->assertSame('DEMO · Red Clínica del Valle', $report->snapshot['data']['client']['name']);
        $report->service->ticket->equipment->client->update(['name' => 'Editado por usuario']);
        $this->seed(\Database\Seeders\WorkflowDemoSeeder::class);
        $this->assertSame(2, Client::count());
        $this->assertSame(3, \App\Modules\TechnicalReports\Models\TechnicalReport::count());
        $this->assertSame('Editado por usuario', $report->service->ticket->equipment->client->fresh()->name);
        $this->assertSame(1, $report->fresh()->revision);
    }
}
