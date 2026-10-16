<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Reports\Actions\QueueReportPdf;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Runs the real database queue and the Python renderer shipped in Docker. */
class ReportPdfIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_worker_renders_actual_pdf_and_removes_intermediate_html(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Storage::fake('local');
        config(['report-pdf.python' => env('REPORT_PDF_PYTHON', '/usr/bin/python3')]);
        $template = ReportTemplate::first();
        $definition = $template->definition;
        $definition['blocks'][0]['header_repeat'] = true;
        $generation = app(QueueReportPdf::class)->execute($template->id, $definition, 24, null);
        $this->assertDatabaseCount('jobs', 1);
        $this->artisan('queue:work', ['connection' => 'database', '--queue' => 'reports', '--once' => true,
            '--tries' => 1, '--timeout' => 210])->assertExitCode(0);
        $this->assertSame('ready', $generation->fresh()->status);
        $bytes = Storage::disk('local')->get($generation->pdfPath());
        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertGreaterThan(10000, strlen($bytes));
        Storage::disk('local')->assertMissing($generation->directory().'/input.html');
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
    }
}
