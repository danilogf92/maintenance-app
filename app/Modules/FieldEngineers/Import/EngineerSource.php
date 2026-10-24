<?php
namespace App\Modules\FieldEngineers\Import;
use Illuminate\Support\Facades\Validator;
class EngineerSource
{
    public function read(string $path): array
    {
        if (!is_file($path)) { throw new \RuntimeException('Falta el archivo de Ingenieros de servicio.'); }
        $rows=[]; $seen=[];
        foreach (new \SplFileObject($path) as $line) {
            if (trim($line)==='') { continue; } $row=json_decode($line,true,512,JSON_THROW_ON_ERROR);
            Validator::make($row,['ninox_table'=>'required|in:T','ninox_id'=>['required','regex:/^[0-9]{1,20}$/'],
                'name'=>'required|string|max:160','email'=>'nullable|email|max:160','phone'=>'nullable|string|max:60',
                'siglas'=>'nullable|string|max:30','country'=>'nullable|string|max:100','city'=>'nullable|string|max:100',
                'photo'=>'present|array|max:0','id_number'=>'nullable|string|max:60','bonus_percentage'=>'nullable|numeric',
                'cost_per_hour'=>'nullable|numeric'])->validate();
            foreach (['visits','tasks','certifications','contract_schedule'] as $field) {
                Validator::make($row,[$field=>'nullable|array',$field.'.*'=>'integer|min:1'])->validate();
            }
            if (isset($seen[$row['ninox_id']])) { throw new \RuntimeException('Referencia de ingeniero repetida en el origen.'); }
            $seen[$row['ninox_id']]=true; $rows[]=$row;
        }
        if (!$rows) { throw new \RuntimeException('El archivo de Ingenieros está vacío.'); }
        return $rows;
    }
}
