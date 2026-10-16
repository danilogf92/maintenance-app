<?php

namespace App\Modules\Reports\Actions;

use App\Modules\Reports\Models\ReportGeneration;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RenderPdfFile
{
    public function execute(ReportGeneration $generation, string $html): void
    {
        $disk = Storage::disk('local');
        $input = $generation->directory().'/input.html';
        $disk->put($input, $html);
        try {
            $process = new Process([config('report-pdf.python'), base_path('scripts/render_report_pdf.py'),
                $disk->path($input), $disk->path($generation->pdfPath())]);
            $process->setTimeout(config('report-pdf.timeout'))->mustRun();
            if (! $disk->exists($generation->pdfPath()) || ! str_starts_with($disk->get($generation->pdfPath()), '%PDF-')) {
                throw new \RuntimeException('El generador no produjo un PDF válido.');
            }
        } finally {
            $disk->delete($input);
        }
    }
}
