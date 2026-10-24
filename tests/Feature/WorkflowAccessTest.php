<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class WorkflowAccessTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;

    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    public function test_technician_only_sees_assigned_work_and_cannot_manage_clients(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico');
        $own = $this->workflow($tech); $other = $this->workflow();
        $this->actingAs($tech);
        $this->assertSame([$own['ticket']->id], VisibleRecords::query('tickets')->pluck('id')->all());
        $this->assertSame([$own['service']->id], VisibleRecords::query('services')->pluck('id')->all());
        $this->assertThrows(fn () => \Livewire\Livewire::test(\App\Modules\Workflow\UI\OperationsPage::class, ['module' => 'services'])->call('edit', $other['service']->id), \Illuminate\Database\Eloquent\ModelNotFoundException::class);
        \Livewire\Livewire::test(\App\Modules\Workflow\UI\OperationsPage::class, ['module' => 'clients'])->call('create')->assertForbidden();
        $this->get('/admin/report-templates')->assertForbidden();
        $this->assertFalse($tech->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')));
    }

    public function test_ceo_can_read_but_cannot_create_or_edit_services(): void
    {
        $f = $this->workflow(); $ceo = User::factory()->create(); $ceo->assignRole('CEO');
        $this->actingAs($ceo);
        $this->get('/operations/services')->assertOk();
        \Livewire\Livewire::test(\App\Modules\Workflow\UI\OperationsPage::class, ['module' => 'services'])->call('edit', $f['service']->id)->assertSet('editingId', $f['service']->id)->call('create')->assertForbidden();
    }

    public function test_unprivileged_user_cannot_access_the_operational_panel(): void
    {
        $user = User::factory()->create(); $this->actingAs($user);
        $this->get('/operations/clients')->assertForbidden();
    }

    public function test_technician_cannot_create_a_service_for_someone_elses_ticket(): void
    {
        $f = $this->workflow(); $tech = User::factory()->create(); $tech->assignRole('Técnico');
        $this->actingAs($tech);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->saveWorkflow('services', ['ticket_id' => $f['ticket']->id, 'service_date' => '2026-10-16',
            'service_type' => 'Intento ajeno', 'status' => 'draft']);
    }
}
