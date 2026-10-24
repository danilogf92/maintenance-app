<?php
namespace App\Modules\Catalogs\Configuration;

class Catalogs
{
    public static function all(): array
    {
        return [
            'manufacturers' => ['label' => 'Fabricantes', 'single' => 'Fabricante', 'prefix' => 'FAB', 'icon' => 'building', 'model' => \App\Modules\Manufacturers\Models\Manufacturer::class],
            'brands' => ['label' => 'Marcas', 'single' => 'Marca', 'prefix' => 'MAR', 'icon' => 'tag', 'model' => \App\Modules\Brands\Models\Brand::class],
            'equipment_models' => ['label' => 'Modelos', 'single' => 'Modelo', 'prefix' => 'MOD', 'icon' => 'cpu', 'model' => \App\Modules\EquipmentModels\Models\EquipmentModel::class],
        ];
    }
    public static function get(string $catalog): array { abort_unless(isset(self::all()[$catalog]), 404); return self::all()[$catalog]; }
    public static function configuration(string $catalog, string $class): string
    {
        $model = self::get($catalog)['model'];
        return substr($model, 0, strrpos($model, '\\Models\\')).'\\Configuration\\'.$class;
    }
    public static function key(string $name, ?int $brand = null): string
    {
        return hash('sha256', ($brand ?? '').'|'.mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name))));
    }
}
