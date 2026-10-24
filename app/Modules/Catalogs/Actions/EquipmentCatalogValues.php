<?php
namespace App\Modules\Catalogs\Actions;

use App\Modules\Brands\Models\Brand;
use App\Modules\EquipmentModels\Models\EquipmentModel;
use Illuminate\Validation\ValidationException;

class EquipmentCatalogValues
{
    public function execute(array $data, $old): array
    {
        if (!array_key_exists('brand_id', $data) && !array_key_exists('equipment_model_id', $data)) {
            if (array_key_exists('brand', $data)) {
                $data['brand_id'] = trim($data['brand'] ?? '') !== '' ? BackfillEquipmentCatalogs::record('brands', $data['brand']) : null;
            } else { $data['brand_id'] = $old?->brand_id; }
            if (array_key_exists('model', $data)) {
                $data['equipment_model_id'] = trim($data['model'] ?? '') !== '' ? BackfillEquipmentCatalogs::record('equipment_models', $data['model'], $data['brand_id']) : null;
            } else { $data['equipment_model_id'] = $old?->equipment_model_id; }
        }
        $brand = !empty($data['brand_id']) ? Brand::lockForUpdate()->findOrFail($data['brand_id']) : null;
        $model = !empty($data['equipment_model_id']) ? EquipmentModel::lockForUpdate()->findOrFail($data['equipment_model_id']) : null;
        if ($model && (int)$model->brand_id !== (int)$brand?->id) {
            throw ValidationException::withMessages(['equipment_model_id' => 'El modelo no pertenece a la marca seleccionada.']);
        }
        foreach (['brand_id' => $brand, 'equipment_model_id' => $model] as $field => $record) {
            if ($record && (int)$old?->$field !== $record->id) {
                \App\Modules\Workflow\Configuration\Permissions::authorize($field === 'brand_id' ? 'brands' : 'equipment_models');
            }
            if ($record && !$record->active && (int)$old?->$field !== $record->id) {
                throw ValidationException::withMessages([$field => 'El registro está inactivo; elige otro.']);
            }
        }
        return [...$data, 'brand_id' => $brand?->id, 'equipment_model_id' => $model?->id, 'brand' => $brand?->name, 'model' => $model?->name];
    }
}
