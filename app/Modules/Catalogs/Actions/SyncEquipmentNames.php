<?php
namespace App\Modules\Catalogs\Actions;

use Illuminate\Support\Facades\DB;

class SyncEquipmentNames
{
    public function execute(string $catalog, $record): void
    {
        $query = DB::table('equipment')->where($catalog === 'brands' ? 'brand_id' : 'equipment_model_id', $record->id);
        $values = $catalog === 'brands' ? ['brand' => $record->name] : ['model' => $record->name,
            'brand_id' => $record->brand_id, 'brand' => $record->brand?->name];
        $query->update([...$values, 'lock_version' => DB::raw('lock_version + 1'), 'updated_at' => now()]);
    }
}
