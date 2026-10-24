<?php
namespace Tests\Feature;

use App\Models\User;
use App\Modules\Reports\Jobs\GenerateReportPdf;
use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\TechnicalReports\Models\TechnicalReport;
use App\Modules\TechnicalReports\Queries\ReportQuery;
use App\Modules\TechnicalReports\UI\TechnicalReportPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class TechnicalReportInterfaceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }
    private function report(array $f): TechnicalReport
    {
        return $this->saveWorkflow('technical_reports', ['service_id' => $f['service']->id,
            'report_template_id' => ReportTemplate::firstOrFail()->id, 'title' => 'Informe de mantenimiento']);
    }
    public function test_native_tabs_and_snapshot_review_use_actual_service_data(): void
    {
        $f = $this->workflow(); $report = $this->report($f);
        $this->get('/operations/technical-reports?record='.$report->id)->assertOk()->assertSee('Información del informe')
            ->assertSee('Revisión')->assertSee('Cliente real de prueba')->assertSee('Verificación eléctrica real')->assertSee('Generar PDF');
    }
    public function test_draft_requires_service_and_template_then_autosaves_once(): void
    {
        $f = $this->workflow();
        $page = Livewire::test(TechnicalReportPage::class)->call('create'); $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors(['service_id', 'report_template_id']);
        $page->set('service_id', (string)$f['service']->id)->set('report_template_id', (string)ReportTemplate::firstOrFail()->id)
            ->call('autosaveDraft', $session)->assertReturned('saved'); $id = $page->get('editingId');
        $page->call('autosaveDraft', $session)->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(1, TechnicalReport::count()); $this->assertSame(1, TechnicalReport::findOrFail($id)->revision);
        $page->set('title', 'Título actualizado')->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(2, TechnicalReport::findOrFail($id)->revision);
    }
    public function test_snapshot_refresh_is_explicit_and_poll_does_not_create_revisions(): void
    {
        $f = $this->workflow(); $report = $this->report($f);
        $page = Livewire::test(TechnicalReportPage::class)->call('edit', $report->id); $session = $page->get('editorSession');
        $f['service']->update(['summary' => 'Resumen actualizado']);
        $page->call('$refresh')->call('autosaveRecord', $report->id, $session)->assertReturned('saved');
        $this->assertSame(1, $report->fresh()->revision);
        $page->call('refreshReportData', $report->id, $session)->assertSee('Resumen actualizado');
        $this->assertSame(2, $report->fresh()->revision);
    }
    public function test_pdf_always_uses_database_reports_queue_and_reuses_pending_request(): void
    {
        Queue::fake(); $f = $this->workflow(); $report = $this->report($f);
        $page = Livewire::test(TechnicalReportPage::class)->call('edit', $report->id); $session = $page->get('editorSession');
        $page->call('generatePdf', $report->id, $session)->assertSee('En cola, esperando al generador.')
            ->call('generatePdf', $report->id, $session)->assertSee('ya tiene un PDF en proceso');
        $this->assertSame(1, ReportGeneration::count());
        Queue::assertPushed(GenerateReportPdf::class, 1);
        Queue::assertPushed(GenerateReportPdf::class, fn ($job) => $job->connection === 'database' && $job->queue === 'reports');
        $page->call('refreshReportData', $report->id, $session)->call('generatePdf', $report->id, $session);
        $this->assertSame(2, ReportGeneration::count());
        $this->assertSame([1, 2], ReportGeneration::orderBy('report_revision')->pluck('report_revision')->all());
    }
    public function test_expired_and_failed_pdf_states_allow_retry(): void
    {
        Queue::fake(); $f = $this->workflow(); $report = $this->report($f);
        $page = Livewire::test(TechnicalReportPage::class)->call('edit', $report->id); $session = $page->get('editorSession');
        $page->call('generatePdf', $report->id, $session); $pdf = ReportGeneration::firstOrFail();
        $pdf->update(['status' => 'failed']); $page->call('$refresh')->assertSee('No se pudo generar el PDF');
        $pdf->update(['expires_at' => now()->subMinute()]); $page->call('$refresh')->assertSee('El enlace temporal expiró');
    }
    public function test_stale_editor_actions_cannot_modify_or_generate_for_other_record(): void
    {
        Queue::fake(); $f = $this->workflow(); $report = $this->report($f); $other = $this->report($f);
        $page = Livewire::test(TechnicalReportPage::class)->call('edit', $report->id); $session = $page->get('editorSession');
        $page->call('edit', $other->id)->call('refreshReportData', $report->id, $session)->call('generatePdf', $report->id, $session);
        Queue::assertNothingPushed(); $this->assertSame(1, $report->fresh()->revision); $this->assertSame(1, $other->fresh()->revision);
    }
    public function test_scope_readonly_and_optimistic_lock_are_preserved(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech); $other = $this->workflow();
        $own = $this->report($f); $foreign = $this->report($other); $this->actingAs($tech);
        $this->get('/operations/technical-reports?record='.$foreign->id)->assertNotFound();
        $this->assertSame([$own->id], app(ReportQuery::class)->records('')->pluck('id')->all());
        $this->assertArrayNotHasKey('snapshot', app(ReportQuery::class)->records('')->first()->getAttributes());
        $page = Livewire::test(TechnicalReportPage::class)->call('edit', $own->id); $session = $page->get('editorSession');
        $own->update(['lock_version' => $own->lock_version + 1]);
        $page->set('title', 'Cambio atrasado')->call('autosaveRecord', $own->id, $session)->assertReturned('invalid')->assertHasErrors('lock_version');
        $reader = User::factory()->create(); $reader->assignRole('CEO'); $this->actingAs($reader);
        Livewire::test(TechnicalReportPage::class)->call('edit', $own->id)->assertSee('Solo lectura')->call('create')->assertForbidden();
        Livewire::test(TechnicalReportPage::class)->call('edit', $own->id)->call('refreshReportData', $own->id, 'wrong')->assertForbidden();
    }
    public function test_contextual_service_shortcut_and_closing_panel_are_safe(): void
    {
        $f = $this->workflow(); $report = $this->report($f); $before = TechnicalReport::count();
        $this->get('/operations/technical-reports?new_for_service='.$f['service']->id)->assertOk()->assertSee('Nuevo registro');
        $this->assertSame($before, TechnicalReport::count());
        $this->get('/operations/technical-reports?new_for_service=bad')->assertNotFound();
        $this->get('/operations/technical-reports?record='.$report->id.'&new_for_service='.$f['service']->id)->assertUnprocessable();
        Livewire::test(TechnicalReportPage::class)->call('edit', $report->id)->call('closeForm')->assertSet('formOpen', false)->assertDontSee('Datos capturados');
    }
}
