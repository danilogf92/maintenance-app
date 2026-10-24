<?php
namespace App\Modules\Equipment\Import;
use App\Modules\Equipment\Models\ReceivedEquipment;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
use App\Modules\EquipmentModels\Models\EquipmentModel;
class ConfirmCatalogLinks
{
    public function execute(): void
    {
        $evidence=[];
        foreach (ReceivedEquipment::all() as $item) {
            $data=$item->payload;
            if (!empty($data['model_id']) && !empty($data['brand_id'])) { $evidence[$data['model_id']][(string)$data['brand_id']]=true; }
        }
        foreach (NinoxCatalogImport::where('catalog','equipment_models')->get() as $import) {
            $brands=$evidence[$import->ninox_id] ?? []; if (count($brands)!==1) { continue; }
            $id=(string)array_key_first($brands); $brand=NinoxCatalogImport::where('catalog','brands')->where('ninox_id',$id)->first();
            if ($brand && EquipmentModel::whereKey($import->record_id)->where('brand_id',$brand->record_id)->exists()) {
                if ($import->brand_basis!=='equipment_reference' || $import->source_brand_id!==$id) {
                    $import->update(['brand_basis'=>'equipment_reference','source_brand_id'=>$id]);
                }
            }
        }
    }
}
