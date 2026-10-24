<?php
namespace App\Modules\Catalogs\Queries;

use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Workflow\Configuration\Permissions;

class CatalogOptions
{
    public static function get(string $catalog, string $search = '', string $selected = '', ?int $brand = null): array
    {
        if (!Permissions::allows($catalog)) { return []; }
        $query = Catalogs::get($catalog)['model']::query();
        if ($catalog === 'equipment_models') { $query->where('brand_id', $brand); }
        $items = (clone $query)->where('active', true)->where('name', 'like', '%'.$search.'%')->orderBy('name')->limit(100)->get();
        if ($selected && ($current = $query->find($selected)) && !$items->contains('id', $current->id)) { $items->prepend($current); }
        return $items->mapWithKeys(fn ($item) => [$item->id => $item->name.($item->active ? '' : ' · Inactivo')])->all();
    }
    public static function parent(string $catalog): ?string
    {
        return match ($catalog) { 'brands' => 'manufacturers', 'equipment_models' => 'brands', default => null };
    }
}
