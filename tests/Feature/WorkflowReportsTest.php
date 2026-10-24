<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Activities\Actions\PrivatePhotos;
use App\Modules\Reports\Actions\RenderPdfFile;
use App\Modules\Reports\Jobs\GenerateReportPdf;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\TechnicalReports\Actions\QueueTechnicalPdf;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class WorkflowReportsTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;

    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); Storage::fake('local'); }

    private function report(array $f)
    {
        return $this->saveWorkflow('technical_reports', ['service_id' => $f['service']->id,
            'report_template_id' => ReportTemplate::first()->id, 'title' => 'Informe del servicio real']);
    }

    public function test_real_report_captures_data_and_template_without_sample_parts(): void
    {
        $f = $this->workflow(); $report = $this->report($f); $snapshot = $report->snapshot;
        $this->assertFalse($snapshot['data']['is_sample']);
        $this->assertSame('Cliente real de prueba', $snapshot['data']['client']['name']);
        $this->assertSame($f['ticket']->code, $snapshot['data']['ticket']['number']);
        $this->assertSame('Verificación eléctrica real', $snapshot['data']['activities'][0]['description']);
        $this->assertSame([], $snapshot['data']['parts']); $this->assertSame([], $snapshot['data']['components']);
        $this->assertStringContainsString('BORRADOR', $snapshot['definition']['page']['footer']);
        $this->assertStringNotContainsString('Cliente real de prueba', DB::table('technical_reports')->where('id', $report->id)->value('snapshot'));
        $f['client']->update(['name' => 'Nombre cambiado posteriormente']);
        $template = ReportTemplate::first(); $definition = $template->definition; $definition['page']['color'] = '#ff0000'; $template->update(['definition' => $definition]);
        $this->assertSame('Cliente real de prueba', $report->fresh()->snapshot['data']['client']['name']);
        $this->assertNotSame('#ff0000', $report->fresh()->snapshot['definition']['page']['color']);
    }

    public function test_snapshot_refresh_is_explicit_and_old_queued_pdf_keeps_its_revision(): void
    {
        Queue::fake(); $f = $this->workflow(); $report = $this->report($f);
        $generation = app(QueueTechnicalPdf::class)->execute($report->id);
        $f['client']->update(['name' => 'Cliente actualizado']);
        $updated = app(SaveRecord::class)->execute('technical_reports', $report->only(['lock_version', 'service_id', 'report_template_id', 'title']), $report->id);
        $this->assertSame(2, $updated->revision);
        $this->assertSame('Cliente actualizado', $updated->snapshot['data']['client']['name']);
        $this->assertSame(1, $generation->report_revision);
        $this->assertSame('Cliente real de prueba', $generation->fresh()->snapshot['data']['client']['name']);
        Queue::assertPushed(GenerateReportPdf::class, fn ($job) => $job->connection === 'database' && $job->queue === 'reports');
    }

    public function test_private_photographs_are_authorized_and_embedded_in_report_snapshot(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech);
        $path = $this->evidence();
        app(SaveRecord::class)->execute('activities', [...$f['activity']->only(['lock_version', 'service_id', 'description', 'result']), 'photos' => [$path]], $f['activity']->id);
        $report = $this->report($f);
        $this->assertStringStartsWith('data:image/png;base64,', $report->snapshot['data']['activities'][0]['photos'][0]['src']);
        $this->actingAs($tech)->get(route('workflow.photo', ['activity' => $f['activity']->id, 'photo' => 0]))->assertOk();
        $other = User::factory()->create(); $other->assignRole('Técnico');
        $this->actingAs($other)->get(route('workflow.photo', ['activity' => $f['activity']->id, 'photo' => 0]))->assertNotFound();
    }

    public function test_another_users_upload_cannot_be_attached_by_path(): void
    {
        $f = $this->workflow(); $path = $this->evidence();
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $this->actingAs($tech);
        $this->assertNull(PrivatePhotos::metadata($path, null));
        $this->expectException(ValidationException::class);
        PrivatePhotos::validate([$path], []);
    }

    public function test_real_pdf_is_generated_from_the_queue_snapshot_and_served_privately(): void
    {
        Queue::fake(); $f = $this->workflow(); $report = $this->report($f);
        $generation = app(QueueTechnicalPdf::class)->execute($report->id);
        (new GenerateReportPdf($generation->id))->handle(app(TemplateRenderer::class), app(RenderPdfFile::class));
        $this->assertSame('ready', $generation->fresh()->status);
        $this->get('/operations/technical-reports')->assertOk();
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($generation->pdfPath()));
        $this->get(route('workflow.pdf', ['report' => $report->id, 'generation' => $generation->id]))->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Cache-Control', 'no-store, private');
        $other = User::factory()->create(); $other->assignRole('Técnico');
        $this->actingAs($other)->get(route('workflow.pdf', ['report' => $report->id, 'generation' => $generation->id]))->assertNotFound();
    }

    public function test_published_design_is_used_even_when_the_template_draft_changes(): void
    {
        $f = $this->workflow(); $template = ReportTemplate::first();
        app(\App\Modules\ReportTemplates\Actions\PublishReportTemplate::class)->execute($template->id);
        $definition = $template->definition; $original = $definition['page']['color'];
        $definition['page']['color'] = '#ff0000'; $template->update(['definition' => $definition]);
        $report = $this->report($f);
        $this->assertSame($original, $report->snapshot['definition']['page']['color']);
        $this->assertSame(1, $report->snapshot['metadata']['template_version']);
    }

    public function test_pdf_identifier_cannot_be_used_for_a_different_report(): void
    {
        Queue::fake(); $f = $this->workflow(); $first = $this->report($f); $second = $this->report($f);
        $generation = app(QueueTechnicalPdf::class)->execute($first->id);
        $this->get(route('workflow.pdf', ['report' => $second->id, 'generation' => $generation->id]))->assertNotFound();
    }
}
