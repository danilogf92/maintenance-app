<?php

namespace App\Modules\Reports\Jobs;

use App\Modules\Reports\Actions\RenderPdfFile;
use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateReportPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 210;
    public int $backoff = 10;

    public function __construct(public string $generationId) {}

    public function handle(TemplateRenderer $renderer, RenderPdfFile $pdf): void
    {
        $generation = ReportGeneration::find($this->generationId);
        if (! $generation || $generation->expires_at->isPast() || $generation->status === 'ready') { return; }
        $generation->update(['status' => 'processing', 'error' => null]);
        $snapshot = $generation->snapshot;
        $pdf->execute($generation, $renderer->execute($snapshot['definition'], $snapshot['data'], $snapshot['branding']));
        $generation->update(['status' => 'ready', 'expires_at' => now()->addMinutes(config('report-pdf.ttl_minutes'))]);
    }

    public function failed(?\Throwable $exception): void
    {
        ReportGeneration::whereKey($this->generationId)->update(['status' => 'failed',
            'error' => 'No se pudo generar el PDF. Reintenta o consulta al administrador.']);
        Log::error('PDF generation failed', ['generation' => $this->generationId, 'error' => $exception?->getMessage()]);
    }
}
