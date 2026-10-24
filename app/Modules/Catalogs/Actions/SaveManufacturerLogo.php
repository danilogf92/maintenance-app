<?php
namespace App\Modules\Catalogs\Actions;

use App\Modules\Manufacturers\Models\Manufacturer;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Support\Facades\{DB, Storage, Validator};
use Illuminate\Validation\ValidationException;

class SaveManufacturerLogo
{
    public function execute(int $id, int $version, $upload = null): Manufacturer
    {
        Permissions::authorize('manufacturers', 'manage');
        if ($upload) { Validator::make(['logo' => $upload], ['logo' => 'required|image|mimes:png,jpg,jpeg,webp|max:1024'])->validate(); }
        $path = null;
        try {
            return DB::transaction(function () use ($id, $version, $upload, &$path) {
                $record = Manufacturer::lockForUpdate()->findOrFail($id);
                if ($record->lock_version !== $version) { throw ValidationException::withMessages(['lock_version' => 'Otro usuario modificó este fabricante. Recarga antes de guardar.']); }
                $path = $upload ? $upload->store('report-assets/manufacturers/'.auth()->id(), 'public') : null;
                $record->update(['logo_path' => $path, 'lock_version' => $version + 1]);
                return $record->fresh();
            });
        } catch (\Throwable $e) {
            if ($path) { Storage::disk('public')->delete($path); }
            throw $e;
        }
    }
}
