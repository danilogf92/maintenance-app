<?php
namespace App\Modules\Catalogs\Actions;

use App\Modules\Catalogs\Configuration\Catalogs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillEquipmentCatalogs
{
    public function execute(): void
    {
        DB::table('equipment')->orderBy('id')->chunkById(200, function ($items) {
            foreach ($items as $item) {
                $brand = trim((string)$item->brand); $model = trim((string)$item->model);
                $brandId = $item->brand_id ?: ($brand !== '' ? self::record('brands', $brand) : null);
                $modelId = $item->equipment_model_id ?: ($model !== '' ? self::record('equipment_models', $model, $brandId) : null);
                DB::table('equipment')->where('id', $item->id)->update(['brand_id' => $brandId, 'equipment_model_id' => $modelId]);
            }
        });
    }
    public static function record(string $table, string $name, ?int $brand = null): int
    {
        $key = Catalogs::key($name, $table === 'equipment_models' ? $brand : null);
        DB::table($table)->insertOrIgnore(['name_key' => $key, 'name' => trim($name),
            'code' => Catalogs::get($table)['prefix'].'-'.Str::upper(Str::random(8)), 'active' => true,
            ...($table === 'equipment_models' ? ['brand_id' => $brand] : []), 'created_at' => now(), 'updated_at' => now()]);
        return (int)DB::table($table)->where('name_key', $key)->value('id');
    }
}
