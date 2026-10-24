<?php
namespace App\Modules\Catalogs\Actions;

use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Validation\ValidationException;

class ValidateCatalogParent
{
    public function execute(string $catalog, array &$data, $old): void
    {
        $relation = match ($catalog) { 'brands' => ['manufacturer_id', 'manufacturers'], 'equipment_models' => ['brand_id', 'brands'], default => null };
        if (!$relation) { return; }
        [$field, $parent] = $relation; $value = (int)($data[$field] ?? 0) ?: null; $data[$field] = $value;
        if ((int)$old?->$field === (int)$value && $old) { return; }
        if ($catalog === 'equipment_models' && !$value && !$old) {
            throw ValidationException::withMessages([$field => 'Selecciona la marca del modelo.']);
        }
        if ($old?->$field && ($old->equipment()->exists() || ($catalog === 'brands' && $old->models()->exists()))) {
            throw ValidationException::withMessages([$field => 'La relación ya está en uso. Crea otro registro para conservar el historial.']);
        }
        if ($value) {
            Permissions::authorize($parent);
            $record = Catalogs::get($parent)['model']::lockForUpdate()->findOrFail($value);
            if (!$record->active) { throw ValidationException::withMessages([$field => 'El registro relacionado está inactivo.']); }
        }
    }
}
