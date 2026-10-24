<?php

namespace App\Modules\Workflow\UI\Controllers;

use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\Storage;

class TechnicalPdfController
{
    public function __invoke(int $report, string $generation)
    {
        $record = VisibleRecords::query('technical_reports')->findOrFail($report);
        $pdf = ReportGeneration::where('technical_report_id', $record->id)->findOrFail($generation);
        abort_if($pdf->expires_at->isPast(), 410, 'El PDF temporal expiró; genera otro desde el informe.');
        abort_unless($pdf->status === 'ready' && Storage::disk('local')->exists($pdf->pdfPath()), 404);

        return response()->file(Storage::disk('local')->path($pdf->pdfPath()), [
            'Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.$record->code.'-R'.$pdf->report_revision.'.pdf"',
            'Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff'])->setPrivate();
    }
}
