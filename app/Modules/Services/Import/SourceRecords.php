<?php
namespace App\Modules\Services\Import;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class SourceRecords
{
    public function read(string $path): array
    {
        if (!is_file($path)) { throw new \RuntimeException('No existe el archivo JSONL de origen.'); }
        $records = []; $seen = [];
        foreach (new \SplFileObject($path) as $line) {
            if (trim($line) === '') { continue; }
            $record = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            Validator::make($record, $this->rules())->validate();
            foreach (['technician_signature', 'report_pdf'] as $field) {
                foreach ($record[$field] as $file) {
                    if ($file['path'] !== 'files/'.$field.'/'.$record['ninox_id'].'/'.$file['name']) {
                        throw new \RuntimeException('El adjunto no pertenece a su bitácora de origen.');
                    }
                }
            }
            $key = $record['ninox_table'].':'.$record['ninox_id'];
            if (isset($seen[$key])) { throw new \RuntimeException('Identificador Ninox repetido en el origen.'); }
            $seen[$key] = true; $records[] = $record;
        }
        if (!$records) { throw new \RuntimeException('El origen no contiene registros.'); }
        return $records;
    }
    private function rules(): array
    {
        $rules = ['ninox_table' => ['required', Rule::in(['K'])], 'ninox_id' => ['required', 'regex:/^[0-9]{1,20}$/'],
            'report_code' => 'required|string|max:30', 'service_date' => 'required|date_format:Y-m-d',
            'service_type_realized' => 'nullable|string|max:100', 'service_type' => 'nullable|string|max:100',
            'tickets_associated' => 'nullable|integer|min:1'];
        foreach (['technician_signature', 'report_pdf'] as $field) {
            $rules[$field] = 'present|array|max:20';
            $rules[$field.'.*.name'] = ['required', 'string', 'max:255', 'not_regex:~[/\\\\]~'];
            $rules[$field.'.*.sha256'] = ['required', 'regex:/^[a-f0-9]{64}$/'];
            $rules[$field.'.*.path'] = ['required', 'regex:~^files/'.$field.'/[0-9]+/[^/\\\\]+$~'];
        }
        return $rules;
    }
}
