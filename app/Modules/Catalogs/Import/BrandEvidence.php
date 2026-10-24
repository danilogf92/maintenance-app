<?php
namespace App\Modules\Catalogs\Import;
class BrandEvidence
{
    public function candidates(array $brands, array $model): ?string
    {
        $index = [];
        foreach ($brands as $brand) {
            foreach ($brand['equipment_id'] ?? [] as $id) { $index[$id][$brand['ninox_id']] = true; }
        }
        $found = [];
        foreach ($model['equipments'] ?? [] as $id) {
            if (count($index[$id] ?? []) !== 1) { return null; }
            $found[(string)array_key_first($index[$id])] = true;
        }
        return count($found) === 1 ? (string)array_key_first($found) : null;
    }
}
