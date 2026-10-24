<?php
namespace App\Modules\Equipment\Import;
use App\Modules\Equipment\Models\{Equipment,ReceivedEquipment};
use App\Modules\Catalogs\Models\NinoxCatalogImport;
use App\Modules\Brands\Models\Brand;
use App\Modules\EquipmentModels\Models\EquipmentModel;
use Illuminate\Support\Facades\DB;
class ImportEquipment
{
    public function execute(array $source): string
    {
        return DB::transaction(function () use ($source) {
            $hash=hash('sha256',json_encode($source,JSON_THROW_ON_ERROR));
            $identity=['source_table'=>$source['ninox_table'],'source_id'=>$source['ninox_id']];
            $import=ReceivedEquipment::where($identity)->lockForUpdate()->first();
            if ($import) { return $import->source_hash===$hash && $import->equipment()->exists() ? 'existing' : 'conflicts'; }
            $brandMap=NinoxCatalogImport::where('catalog','brands')->where('ninox_id',(string)($source['brand_id'] ?? ''))->first();
            $modelMap=NinoxCatalogImport::where('catalog','equipment_models')->where('ninox_id',(string)($source['model_id'] ?? ''))->first();
            $brand=$brandMap ? Brand::find($brandMap->record_id) : null;
            $model=$modelMap ? EquipmentModel::find($modelMap->record_id) : null;
            $code='EQ-REC-'.$source['ninox_id']; if (Equipment::where('code',$code)->exists()) { return 'conflicts'; }
            $equipment=Equipment::create(['code'=>$code,'name'=>trim($source['main_component'] ?? '') ?: ($model?->name ?? 'Equipo · referencia '.$source['ninox_id']),
                'serial'=>$source['serial_number'] ?? null, 'brand_id'=>$brand?->id,'brand'=>$brand?->name,
                'equipment_model_id'=>$model?->id,'model'=>$model?->name,'client_id'=>null,'institution_id'=>null,
                'status'=>'unknown','lock_version'=>1]);
            ReceivedEquipment::create([...$identity,'equipment_id'=>$equipment->id,'source_hash'=>$hash,'payload'=>$source]);
            return 'created';
        });
    }
}
