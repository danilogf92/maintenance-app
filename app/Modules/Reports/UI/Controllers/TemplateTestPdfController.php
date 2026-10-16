<?php

namespace App\Modules\Reports\UI\Controllers;

use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\ReportTemplates\Configuration\Permissions;
use Illuminate\Support\Facades\Storage;

class TemplateTestPdfController
{
    public function __invoke(string $generation)
    {
        Permissions::authorize();
        $record = ReportGeneration::where('user_id', auth()->id())->findOrFail($generation);
        abort_if($record->expires_at->isPast(), 410, 'El PDF temporal expiró. Genera otra prueba.');
        abort_unless($record->status === 'ready' && Storage::disk('local')->exists($record->pdfPath()), 404);

        return response()->file(Storage::disk('local')->path($record->pdfPath()), [
            'Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="prueba-informe.pdf"',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
