<?php
namespace App\Modules\Services\Import;
use App\Modules\Services\Models\{Service, ServiceDocument};
use Illuminate\Support\Facades\Storage;
class ImportDocuments
{
    public function execute(Service $service, array $record, array $roots): array
    {
        $counts = ['ready' => 0, 'missing' => 0, 'invalid' => 0];
        foreach (['technician_signature', 'report_pdf'] as $kind) {
            foreach ($record[$kind] as $reference) {
                $document = ServiceDocument::firstOrCreate(['service_id' => $service->id, 'kind' => $kind, 'sha256' => $reference['sha256']], ['name' => $reference['name']]);
                $status = $this->copy($document, $reference, $roots);
                $counts[$status]++;
            }
        }
        return $counts;
    }
    private function copy(ServiceDocument $document, array $reference, array $roots): string
    {
        $disk = Storage::disk('local');
        if ($document->status === 'ready' && $document->path && $disk->exists($document->path)
            && hash_file('sha256', $disk->path($document->path)) === $document->sha256) { return 'ready'; }
        $file = $this->locate($reference['path'], $roots);
        $status = !$file ? 'missing' : ($this->valid($file, $document) ? 'ready' : 'invalid');
        $path = null;
        if ($status === 'ready') {
            $extension = $document->kind === 'report_pdf' ? 'pdf' : 'png';
            $path = 'ninox/services/'.$document->service_id.'/'.$document->sha256.'.'.$extension;
            $stream = fopen($file, 'rb');
            try { if (!$disk->put($path, $stream)) { throw new \RuntimeException('No se pudo guardar un documento privado.'); } }
            finally { fclose($stream); }
        }
        $document->update(['status' => $status, 'path' => $path]);
        return $status;
    }
    private function locate(string $relative, array $roots): ?string
    {
        foreach ($roots as $root) {
            $base = realpath($root); $file = $base ? realpath($base.DIRECTORY_SEPARATOR.$relative) : false;
            if ($file && str_starts_with($file, $base.DIRECTORY_SEPARATOR) && is_file($file)) { return $file; }
        }
        return null;
    }
    private function valid(string $file, ServiceDocument $document): bool
    {
        if (filesize($file) > 100 * 1024 * 1024 || hash_file('sha256', $file) !== $document->sha256) { return false; }
        if ($document->kind === 'report_pdf') {
            $stream = fopen($file, 'rb'); try { return fread($stream, 5) === '%PDF-'; } finally { fclose($stream); }
        }
        return (@getimagesize($file)[2] ?? null) === IMAGETYPE_PNG;
    }
}
