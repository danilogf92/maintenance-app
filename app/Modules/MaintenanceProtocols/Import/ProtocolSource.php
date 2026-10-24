<?php
namespace App\Modules\MaintenanceProtocols\Import;
use Illuminate\Support\Facades\Validator;
class ProtocolSource
{
    public function read(string $directory): array
    {
        $protocols = $this->rows($directory.'/protocols.jsonl', [
            'ninox_table' => 'required|in:GB', 'protocol_name' => 'nullable|string|max:255',
            'protocol_code' => 'nullable|string|max:255', 'protocol_description' => 'nullable|string|max:60000',
            'protocol_version' => 'nullable|string|max:255', 'protocol_type' => 'nullable|string|max:255',
            'brand_and_model_info' => 'nullable|string|max:255', 'is_obsolete' => 'nullable|boolean',
            'protocol_steps' => 'present|array', 'protocol_steps.*' => 'integer|min:1|distinct',
        ]);
        $steps = $this->rows($directory.'/steps.jsonl', [
            'ninox_table' => 'required|in:HB', 'protocol_id' => 'nullable|integer|min:1',
            'instruction' => 'nullable|string|max:60000', 'reports_indication' => 'nullable|string|max:60000',
            'range_min_max' => 'nullable|string|max:255', 'type' => 'nullable|string|max:255',
            'step_number' => 'nullable|integer|min:0', 'is_add_to_report' => 'required|boolean',
            'image_suggested_report' => 'present|array',
        ]);
        foreach ($protocols as $protocol) {
            foreach ($protocol['protocol_steps'] as $id) {
                if (!isset($steps[$id]) || (string)($steps[$id]['protocol_id'] ?? '') !== $protocol['ninox_id']) {
                    throw new \RuntimeException('Los pasos del protocolo '.$protocol['ninox_id'].' no coinciden con su relación.');
                }
            }
        }
        foreach ($steps as $step) {
            if (isset($protocols[$step['protocol_id'] ?? '']) && !in_array((int)$step['ninox_id'], $protocols[$step['protocol_id']]['protocol_steps'], true)) {
                throw new \RuntimeException('Paso no incluido en el protocolo padre: '.$step['ninox_id']);
            }
        }
        $files = json_decode(file_get_contents($directory.'/images.json'), true, 512, JSON_THROW_ON_ERROR);
        Validator::make(['files' => $files], ['files' => 'present|array', 'files.*.step_source_id' => 'required',
            'files.*.sha256' => 'required|regex:/^[a-f0-9]{64}$/', 'files.*.name' => 'required|string|max:255',
            'files.*.path' => ['required', 'regex:~^images/[a-f0-9]{64}\.(png|jpg|jpeg|webp|pdf)$~']])->validate();
        $actual = []; $expected = [];
        foreach ($steps as $step) {
            foreach ($step['image_suggested_report'] as $file) { $expected[$step['ninox_id'].'/'.$file['sha256']] = true; }
        }
        foreach ($files as &$file) {
            $key = $file['step_source_id'].'/'.$file['sha256'];
            if (!isset($expected[$key]) || isset($actual[$key])) { throw new \RuntimeException('Adjunto duplicado o sin paso confirmado.'); }
            $actual[$key] = true; $path = $directory.'/'.$file['path'];
            if (!is_file($path) || hash_file('sha256', $path) !== $file['sha256']) { throw new \RuntimeException('Archivo ausente o alterado: '.$file['name']); }
            $file['mime'] = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            if (!in_array($file['mime'], ['image/png', 'image/jpeg', 'image/webp', 'application/pdf'], true)) {
                throw new \RuntimeException('Tipo de adjunto no permitido.');
            }
        }
        unset($file);
        if (array_diff_key($expected, $actual)) { throw new \RuntimeException('Faltan adjuntos de los pasos.'); }
        return compact('protocols', 'steps', 'files');
    }
    private function rows(string $path, array $rules): array
    {
        if (!is_file($path)) { throw new \RuntimeException('Falta el archivo de protocolos o pasos.'); }
        $rows = [];
        foreach (new \SplFileObject($path) as $line) {
            if (trim($line) === '') { continue; }
            $row = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            Validator::make($row, ['ninox_id' => ['required', 'regex:/^[0-9]{1,20}$/'], ...$rules])->validate();
            if (isset($rows[$row['ninox_id']])) { throw new \RuntimeException('Identidad de origen duplicada.'); }
            $rows[$row['ninox_id']] = $row;
        }
        if (!$rows) { throw new \RuntimeException('El archivo recibido está vacío.'); }
        return $rows;
    }
}
