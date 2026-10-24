<?php
namespace App\Modules\Catalogs\Queries;

use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;

class CatalogQuery
{
    public function records(string $catalog, string $search, string $active)
    {
        Permissions::authorize($catalog);
        $query = Catalogs::get($catalog)['model']::query();
        $parent = CatalogOptions::parent($catalog);
        if ($parent && Permissions::allows($parent)) { $query->with($catalog === 'brands' ? 'manufacturer' : 'brand'); }
        return $query->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')))
            ->when($active !== '', fn ($q) => $q->where('active', $active === '1'))->orderBy('name')->orderBy('id')->get();
    }
    public function related(string $catalog, int $id): array
    {
        [$child, $field] = match ($catalog) { 'manufacturers' => ['brands', 'manufacturer_id'], 'brands' => ['equipment_models', 'brand_id'], default => ['equipment', 'equipment_model_id'] };
        if (!Permissions::allows($child)) { return []; }
        $query = $child === 'equipment' ? VisibleRecords::query($child) : Catalogs::get($child)['model']::query();
        return ['label' => $child === 'equipment' ? 'Equipos' : Catalogs::get($child)['label'], 'module' => $child,
            'items' => $query->where($field, $id)->orderBy('name')->limit(51)->get()];
    }
}
