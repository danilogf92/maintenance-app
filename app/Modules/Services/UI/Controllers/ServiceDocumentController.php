<?php
namespace App\Modules\Services\UI\Controllers;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\Storage;
class ServiceDocumentController
{
    public function __invoke(int $service, int $document)
    {
        $record = VisibleRecords::query('services')->findOrFail($service);
        $file = $record->documents()->whereKey($document)->where('status', 'ready')->firstOrFail();
        abort_unless($file->path && str_starts_with($file->path, 'ninox/services/'.$record->id.'/'), 404);
        $disk = Storage::disk('local'); abort_unless($disk->exists($file->path), 404);
        return response()->file($disk->path($file->path), ['Content-Type' => $file->kind === 'report_pdf' ? 'application/pdf' : 'image/png',
            'Content-Disposition' => 'inline; filename="documento-'.(int)$file->id.($file->kind === 'report_pdf' ? '.pdf' : '.png').'"',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => "sandbox"]);
    }
}
