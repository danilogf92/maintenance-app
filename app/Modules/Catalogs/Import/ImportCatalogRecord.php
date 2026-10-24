<?php
namespace App\Modules\Catalogs\Import;
use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
use Illuminate\Support\Facades\DB;
class ImportCatalogRecord
{
    public function execute(string $catalog, array $data, ?string $brandId, string $root): array
    {
        return DB::transaction(function () use ($catalog, $data, $brandId, $root) {
            $hash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
            $import = NinoxCatalogImport::where('ninox_table', $data['ninox_table'])->where('ninox_id', $data['ninox_id'])->lockForUpdate()->first();
            if ($import && $import->source_hash !== $hash) { return ['conflicts' => 1]; }
            $created = !$import; $definition = Catalogs::get($catalog); $model = $definition['model'];
            if (!$import) {
                $brand = $brandId ? NinoxCatalogImport::where('catalog', 'brands')->where('ninox_id', $brandId)->first() : null;
                $values = $this->values($catalog, $data, $brand?->record_id);
                if ($model::where('name_key', $values['name_key'])->orWhere('code', $values['code'])->exists()) { return ['conflicts' => 1]; }
                $record = $model::create($values);
                $import = NinoxCatalogImport::create(['ninox_table' => $data['ninox_table'], 'ninox_id' => $data['ninox_id'],
                    'catalog' => $catalog, 'record_id' => $record->id, 'source_hash' => $hash, 'payload' => $data,
                    'brand_basis' => $catalog === 'equipment_models' && $brand ? 'shared_equipment' : null, 'source_brand_id' => $brand?->ninox_id]);
            } elseif (!$model::whereKey($import->record_id)->exists()) { return ['conflicts' => 1]; }
            $images = app(CatalogImages::class)->execute($import, $data[$catalog === 'brands' ? 'logo' : 'model_image'], $root);
            return [$created ? 'created' : 'existing' => 1, ...$images,
                'models_linked' => $catalog === 'equipment_models' && $import->source_brand_id ? 1 : 0,
                'models_pending' => $catalog === 'equipment_models' && !$import->source_brand_id ? 1 : 0];
        });
    }
    private function values(string $catalog, array $data, ?int $brand): array
    {
        $isBrand = $catalog === 'brands'; $name = trim($data[$isBrand ? 'brand_name' : 'model_name']);
        return ['name' => $name, 'name_key' => Catalogs::key($name, $isBrand ? null : $brand),
            'code' => 'NX-'.$data['ninox_table'].'-'.$data['ninox_id'], 'active' => true, ...($isBrand
                ? ['distributor' => $data['distributor'] ?? null, 'origin' => $data['origin'] ?? null]
                : ['brand_id' => $brand, 'description' => $data['description'] ?? null, 'visits_recommended_per_year' => $data['visits_recommended_per_year'] ?? null])];
    }
}
