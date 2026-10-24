<?php
namespace App\Modules\Catalogs\Import;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
use Illuminate\Support\Facades\Storage;
class CatalogImages
{
    public function execute(NinoxCatalogImport $import, array $files, string $root): array
    {
        $counts = ['images_ready' => 0, 'images_missing' => 0, 'images_invalid' => 0];
        foreach ($files as $file) {
            $asset = $import->assets()->firstOrCreate(['sha256' => $file['sha256']], ['name' => $file['name'], 'status' => 'missing']);
            $base = realpath($root); $path = $base ? realpath($base.'/'.$file['path']) : false;
            $status = 'missing'; $stored = null; $mime = null;
            if ($path && str_starts_with($path, $base.DIRECTORY_SEPARATOR) && is_file($path)) {
                $mime = $this->mime($path, $file['sha256']); $status = $mime ? 'ready' : 'invalid';
                if ($mime) {
                    $stored = 'ninox/catalogs/'.$import->id.'/'.$file['sha256']; $disk = Storage::disk('local');
                    if (!$disk->exists($stored) || hash_file('sha256', $disk->path($stored)) !== $file['sha256']) {
                        $stream = fopen($path, 'rb');
                        try { if (!$disk->put($stored, $stream)) { throw new \RuntimeException('No se pudo guardar la imagen privada.'); } }
                        finally { fclose($stream); }
                    }
                }
            }
            $asset->update(['status' => $status, 'path' => $stored, 'mime' => $mime]); $counts['images_'.$status]++;
        }
        return $counts;
    }
    private function mime(string $path, string $hash): ?string
    {
        if (filesize($path) > 20 * 1024 * 1024 || hash_file('sha256', $path) !== $hash) { return null; }
        return match (@getimagesize($path)[2] ?? null) { IMAGETYPE_PNG => 'image/png', IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_GIF => 'image/gif', IMAGETYPE_WEBP => 'image/webp', default => null };
    }
}
