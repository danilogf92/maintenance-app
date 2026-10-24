<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Reports\Actions\QueueReportPdf;
use App\Modules\Reports\Jobs\GenerateReportPdf;
use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Storage::fake('local');
        Queue::fake();
    }

    private function requestPdf(): ReportGeneration
    {
        $template = ReportTemplate::first();
        return app(QueueReportPdf::class)->execute($template->id, $template->definition, 24, null);
    }

    public function test_every_pdf_goes_to_database_queue_with_immutable_snapshot(): void
    {
        $generation = $this->requestPdf();
        $this->assertSame('queued', $generation->status);
        $this->assertCount(0, $generation->snapshot['data']['activities']);
        $template = ReportTemplate::first();
        $definition = $template->definition; $definition['blocks'][0]['title'] = 'Posterior';
        $template->update(['definition' => $definition]);
        $this->assertNotSame('Posterior', $generation->fresh()->snapshot['definition']['blocks'][0]['title']);
        Queue::assertPushed(GenerateReportPdf::class, fn ($job) => $job->connection === 'database' && $job->queue === 'reports');
        $this->assertStringNotContainsString('EJEMPLO-2318', $generation->getRawOriginal('snapshot'));
    }

    public function test_pdf_is_private_and_expires(): void
    {
        $generation = $this->requestPdf();
        $generation->update(['status' => 'ready']);
        Storage::disk('local')->put($generation->pdfPath(), '%PDF-1.4 test');
        $this->get(route('reports.preview', $generation->id))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $other = User::factory()->create(['can_access_admin' => true]); $other->assignRole('SuperAdmin');
        $this->actingAs($other)->get(route('reports.preview', $generation->id))->assertNotFound();
        $this->actingAs(User::find($generation->user_id));
        $generation->update(['expires_at' => now()->subMinute()]);
        $this->get(route('reports.preview', $generation->id))->assertStatus(410);
        $this->artisan('reports:clean-previews')->assertExitCode(0);
        Storage::disk('local')->assertMissing($generation->pdfPath());
        $this->assertDatabaseMissing('report_generations', ['id' => $generation->id]);
    }

    public function test_failed_job_shows_safe_error(): void
    {
        $generation = $this->requestPdf();
        (new GenerateReportPdf($generation->id))->failed(new \RuntimeException('Secret path /etc/private'));
        $this->assertSame('failed', $generation->fresh()->status);
        $this->assertStringNotContainsString('private', $generation->fresh()->error);
    }

    public function test_preview_cannot_exceed_pending_job_limit(): void
    {
        $this->requestPdf(); $this->requestPdf(); $this->requestPdf();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->requestPdf();
    }
}
