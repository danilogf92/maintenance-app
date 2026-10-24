<?php
namespace App\Modules\Tickets\Import;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class TicketSource
{
    public function read(string $path): array
    {
        if (!is_file($path)) { throw new \RuntimeException('Falta el JSONL de Tickets.'); }
        $records=[]; $seen=[]; $codes=[];
        foreach (new \SplFileObject($path) as $line) {
            if (trim($line)==='') { continue; }
            $data=json_decode($line,true,512,JSON_THROW_ON_ERROR);
            Validator::make($data,['ninox_table'=>['required',Rule::in(['F'])], 'ninox_id'=>['required','regex:/^[0-9]{1,20}$/'],
                'ticket_code'=>'required|string|max:30', 'ticket_date'=>'required|date_format:Y-m-d', 'status'=>'required|string|max:255',
                'report_client'=>'nullable|string|max:255','technical_info'=>'nullable|string|max:60000', 'value_16'=>'nullable|integer|min:1',
                'value_15'=>'nullable|array','value_15.*'=>'integer|min:1', 'field_service_engineer'=>'nullable|array',
                'value_19'=>'present|array|max:0'])->validate();
            if (isset($seen[$data['ninox_id']]) || isset($codes[$data['ticket_code']])) { throw new \RuntimeException('Identidad o código de ticket repetido en el origen.'); }
            $seen[$data['ninox_id']]=true; $codes[$data['ticket_code']]=true; $records[]=$data;
        }
        if (!$records) { throw new \RuntimeException('La exportación de Tickets está vacía.'); }
        return $records;
    }
}
