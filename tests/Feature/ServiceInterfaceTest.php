<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Services\Models\Service;
use App\Modules\Services\Queries\{ServiceOptions, ServiceQuery};
use App\Modules\Services\UI\{ServicePage, RelatedActivities, RelatedReports};
use App\Modules\TechnicalReports\Models\TechnicalReport;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class ServiceInterfaceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    private function report(int $serviceId): TechnicalReport
    {
        $this->seed(\Database\Seeders\ReportTemplatesSeeder::class);
        return $this->saveWorkflow('technical_reports', ['service_id' => $serviceId,
            'report_template_id' => \App\Modules\ReportTemplates\Models\ReportTemplate::firstOrFail()->id, 'title' => 'Informe de prueba']);
    }

    public function test_native_route_four_tabs_and_date_format(): void
    {
        $f = $this->workflow();
        $this->assertSame('services-page', app(\Livewire\Mechanisms\ComponentRegistry::class)->getName(ServicePage::class));
        $this->get('/operations/services?record='.$f['service']->id)->assertOk()->assertSee('Información del servicio')->assertSee('Intervención');
        Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->assertSet('service_date', '2026-10-16');
        $this->get('/operations/services?record=bad')->assertNotFound();
    }

    public function test_create_autosaves_once_and_inherits_ticket_technician(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech);
        $page = Livewire::test(ServicePage::class)->call('create'); $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors('ticket_id');
        $page->set('ticket_id', (string)$f['ticket']->id)->call('autosaveDraft', $session)->assertReturned('saved');
        $id = $page->get('editingId');
        $page->call('autosaveDraft', $session)->assertReturned('saved')->set('summary', 'Intervención nueva')
            ->call('autosaveRecord', $id, $session)->assertReturned('saved')->set('status', 'completed')
            ->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(2, Service::count()); $this->assertSame($tech->id, Service::findOrFail($id)->technician_id);
        $this->assertSame(3, Service::findOrFail($id)->lock_version);
    }

    public function test_both_relations_load_independently_and_reset_when_record_changes(): void
    {
        $f = $this->workflow(); $other = $this->workflow(); $report = $this->report($f['service']->id);
        $page = Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->assertSet('loadedEditorRelations', []);
        $page->call('loadEditorRelation', 'activities')->assertSet('loadedEditorRelations.activities', true)
            ->assertDontSee($report->code)->call('loadEditorRelation', 'technical_reports')->assertSet('loadedEditorRelations.technical_reports', true)
            ->assertSee($report->code)->call('edit', $other['service']->id)->assertSet('loadedEditorRelations', []);
    }

    public function test_related_children_scope_parent_and_actor_without_loading_snapshot(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech); $other = $this->workflow();
        $ownReport = $this->report($f['service']->id); $foreignReport = $this->report($other['service']->id); $this->actingAs($tech);
        Livewire::test(RelatedActivities::class, ['serviceId' => $f['service']->id])->assertSee($f['activity']->code)->assertDontSee($other['activity']->code);
        $page = Livewire::test(RelatedReports::class, ['serviceId' => $f['service']->id])->assertSee($ownReport->code)->assertDontSee($foreignReport->code);
        $this->assertArrayNotHasKey('snapshot', $page->viewData('items')->first()->getAttributes());
        $this->get('/operations/services?record='.$other['service']->id)->assertNotFound();
        $this->assertThrows(fn () => Livewire::test(RelatedReports::class, ['serviceId' => $other['service']->id]), \Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }

    public function test_history_freezes_ticket_but_allows_intervention_changes(): void
    {
        $f = $this->workflow(); $other = $this->workflow();
        $page = Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->assertSet('relationsFrozen', true);
        $session = $page->get('editorSession');
        $page->set('ticket_id', (string)$other['ticket']->id)->call('autosaveRecord', $f['service']->id, $session)->assertReturned('invalid')->assertHasErrors('ticket_id');
        $page->set('ticket_id', (string)$f['ticket']->id)->set('recommendations', "Cambiar cable\nRevisar conector")
            ->call('autosaveRecord', $f['service']->id, $session)->assertReturned('saved');
        $this->assertSame($f['ticket']->id, $f['service']->fresh()->ticket_id);
        $this->assertStringContainsString('Cambiar cable', $f['service']->fresh()->recommendations);
    }

    public function test_ceo_can_consult_but_cannot_create_save_or_delete(): void
    {
        $f = $this->workflow(); $ceo = User::factory()->create(); $ceo->assignRole('CEO'); $this->actingAs($ceo);
        Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->assertSee('Solo lectura')->call('create')->assertForbidden();
        $page = Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->set('summary', 'Intento');
        $page->call('autosaveRecord', $f['service']->id, $page->get('editorSession'))->assertForbidden();
        Livewire::test(ServicePage::class)->call('requestOpenRecordDelete')->assertForbidden();
    }

    public function test_relation_permissions_hide_tabs_and_counts_and_deny_direct_load(): void
    {
        $f = $this->workflow(); $reader = User::factory()->create();
        $reader->givePermissionTo(['workflow.access', 'workflow.view_all', 'services.view', 'technical_reports.view']); $this->actingAs($reader);
        $page = Livewire::test(ServicePage::class)->call('edit', $f['service']->id);
        $this->assertArrayNotHasKey('activities', $page->viewData('availableColumns'));
        $this->assertArrayNotHasKey('equipment', $page->viewData('availableColumns'));
        $page->call('loadEditorRelation', 'activities')->assertForbidden();
        Livewire::test(RelatedActivities::class, ['serviceId' => $f['service']->id])->assertForbidden();
        Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->call('loadEditorRelation', 'technical_reports')->assertSet('loadedEditorRelations.technical_reports', true);
    }

    public function test_contextual_ticket_shortcut_is_a_draft_and_rejects_foreign_ticket(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech); $other = $this->workflow();
        $count = Service::count(); $this->actingAs($tech);
        $this->get('/operations/services?new_for_ticket='.$f['ticket']->id)->assertOk()->assertSee('Nuevo registro');
        $this->assertSame($count, Service::count());
        $this->get('/operations/services?new_for_ticket='.$other['ticket']->id)->assertNotFound();
        $this->get('/operations/services?new_for_ticket=invalid')->assertNotFound();
        $this->get('/operations/services?record='.$f['service']->id.'&new_for_ticket='.$f['ticket']->id)->assertUnprocessable();
    }

    public function test_context_and_ticket_search_never_expose_another_technicians_ticket(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $own = $this->workflow($tech); $other = $this->workflow();
        $this->actingAs($tech);
        $this->assertSame([], ServiceOptions::context((string)$other['ticket']->id, null));
        $options = ServiceOptions::tickets('no-match', (string)$own['ticket']->id);
        $this->assertArrayHasKey($own['ticket']->id, $options); $this->assertArrayNotHasKey($other['ticket']->id, $options);
        $this->assertSame($own['equipment']->id, ServiceOptions::context((string)$own['ticket']->id, $own['service']->id)['equipment']->id);
        $this->assertSame([$own['service']->id], VisibleRecords::query('services')->pluck('id')->all());
    }

    public function test_cache_counters_invalidate_and_concurrent_update_is_rejected(): void
    {
        $f = $this->workflow(); $query = app(ServiceQuery::class);
        $this->assertSame(0, $query->records('', '')->first()->reports_count);
        $this->report($f['service']->id);
        $this->assertSame(1, $query->records('', '')->first()->reports_count);
        $this->assertSame(1, $query->records('', '')->first()->activities_count);
        $page = Livewire::test(ServicePage::class)->call('edit', $f['service']->id);
        $f['service']->update(['summary' => 'Cambio externo', 'lock_version' => $f['service']->lock_version + 1]);
        $page->set('summary', 'Cambio atrasado')->call('autosaveRecord', $f['service']->id, $page->get('editorSession'))
            ->assertReturned('invalid')->assertHasErrors('lock_version');
        $this->assertSame('Cambio externo', $f['service']->fresh()->summary);
    }
}
