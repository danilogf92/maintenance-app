<?php
namespace App\Modules\Catalogs\UI;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Support\Facades\Storage;
class CatalogAssetController
{
    public function __invoke(string $catalog, int $record, int $asset)
    {
        abort_unless(in_array($catalog, ['brands', 'equipment_models'], true), 404); Permissions::authorize($catalog);
        $import = NinoxCatalogImport::where('catalog', $catalog)->where('record_id', $record)->firstOrFail();
        $file = $import->assets()->whereKey($asset)->where('status', 'ready')->firstOrFail();
        $disk = Storage::disk('local');
        abort_unless($file->path && str_starts_with($file->path, 'ninox/catalogs/'.$import->id.'/') && $disk->exists($file->path), 404);
        return response()->file($disk->path($file->path), ['Content-Type' => $file->mime, 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store', 'Content-Security-Policy' => 'sandbox']);
    }
}
