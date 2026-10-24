<?php
namespace App\Modules\Equipment\Import;
use Illuminate\Support\Facades\Validator;
class EquipmentSource
{
    public function read(string $path): array
    {
        if (!is_file($path)) { throw new \RuntimeException('Falta el archivo de Equipos.'); }
        $rows=[]; $ids=[];
        foreach (new \SplFileObject($path) as $line) {
            if (trim($line)==='') { continue; }
            $row=json_decode($line,true,512,JSON_THROW_ON_ERROR);
            Validator::make($row,['ninox_table'=>'required|in:C','ninox_id'=>['required','regex:/^[0-9]{1,20}$/'],
                'brand_id'=>'nullable|integer|min:1','model_id'=>'nullable|integer|min:1','serial_number'=>'nullable|string|max:100',
                'main_component'=>'nullable|string|max:160','manufactured_at'=>'nullable|date_format:Y-m-d',
                'date_last_maintenance'=>'nullable|date_format:Y-m-d','start_date_schedule'=>'nullable|date_format:Y-m-d',
                'pvp_ecu'=>'nullable|numeric','ec_cost'=>'nullable|numeric','value_25'=>'nullable|numeric','value_33'=>'nullable|numeric',
                'annual_visits'=>'nullable|string|max:100','owner'=>'nullable|string|max:100',
                'equipment_img'=>'present|array|max:0','signature'=>'present|array|max:0','report_pdf'=>'present|array|max:0'])->validate();
            foreach (['tickets','equipment_medical_centers_history','customer_history','contract_associated','contract_schedule','equipment_components','conctacts_users'] as $field) {
                Validator::make($row,[$field=>'nullable|array',$field.'.*'=>'integer|min:1'])->validate();
            }
            if (isset($ids[$row['ninox_id']])) { throw new \RuntimeException('Referencia de equipo repetida en el archivo.'); }
            $ids[$row['ninox_id']]=true; $rows[]=$row;
        }
        if (!$rows) { throw new \RuntimeException('El archivo de Equipos está vacío.'); }
        return $rows;
    }
}
