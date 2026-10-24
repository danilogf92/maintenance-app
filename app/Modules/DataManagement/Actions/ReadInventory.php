<?php
namespace App\Modules\DataManagement\Actions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class ReadInventory
{
    public function execute(string $path): array
    {
        if (!is_file($path) || filesize($path) > 25 * 1024 * 1024) { $this->invalid('El diagnóstico no existe o supera 25 MiB.'); }
        $stream = fopen($path, 'rb'); $signature = fread($stream, 4); fclose($stream);
        $issuesJson = null;
        if (str_starts_with($signature, 'PK')) {
            $zip = new \ZipArchive;
            if ($zip->open($path, \ZipArchive::RDONLY) !== true) { $this->invalid('El ZIP no es válido.'); }
            try {
                $matches = [];
                if ($zip->numFiles > 20) { $this->invalid('Sube el ZIP de diagnóstico, no los datos originales.'); }
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (preg_match('~(^|/)carpetas\.json$~', $name)) { $matches[] = $i; }
                }
                if (count($matches) !== 1 || $zip->statIndex($matches[0])['size'] > 25 * 1024 * 1024) { $this->invalid('El ZIP debe contener un único carpetas.json de hasta 25 MiB.'); }
                $json = $zip->getFromIndex($matches[0]);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    if (preg_match('~(^|/)errors\.json$~', $zip->getNameIndex($i))) {
                        if ($zip->statIndex($i)['size'] > 2 * 1024 * 1024) { $this->invalid('errors.json supera 2 MiB.'); }
                        $issuesJson = $zip->getFromIndex($i); break;
                    }
                }
            } finally { $zip->close(); }
        } else {
            $json = file_get_contents($path); $sidecar = dirname($path).'/errors.json';
            if (is_file($sidecar) && filesize($sidecar) <= 2 * 1024 * 1024) { $issuesJson = file_get_contents($sidecar); }
        }
        try { $data = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $json), true, 64, JSON_THROW_ON_ERROR); }
        catch (\Throwable) { $this->invalid('El inventario no contiene JSON válido.'); }
        if (!is_array($data)) { $this->invalid('El inventario debe contener un objeto JSON.'); }
        Validator::make($data, ['operation' => 'required|in:inventario', 'run_id' => 'required|string|max:100',
            'source' => 'required|string|max:2048', 'tables' => 'present|array|max:1000',
            'tables.*.name' => 'required|string|max:255', 'tables.*.folder' => 'required|string|max:2048',
            'tables.*.source_table_ids' => 'present|array|max:20', 'tables.*.source_table_ids.*' => 'string|max:40',
            'tables.*.records_valid' => 'required|integer|min:0|max:100000000', 'tables.*.records_invalid' => 'required|integer|min:0|max:100000000',
            'tables.*.attachments' => 'required|integer|min:0|max:100000000', 'tables.*.fields' => 'present|array|max:1000',
            'tables.*.fields.*' => 'string|max:255', 'tables.*.status' => 'required|string|max:80'])->validate();
        $tables = []; $seen = [];
        foreach ($data['tables'] as $row) {
            if (isset($seen[$row['folder']])) { $this->invalid('El inventario repite una carpeta.'); }
            $seen[$row['folder']] = true;
            $tables[] = array_intersect_key($row, array_flip(['name', 'folder', 'source_table_ids', 'records_valid', 'records_invalid', 'attachments', 'fields', 'status']));
        }
        return ['operation' => 'inventario', 'run_id' => $data['run_id'], 'source' => $data['source'], 'tables' => $tables,
            'loaded_at' => now()->toIso8601String(), 'issues' => $this->issues($issuesJson), 'imported_records' => 0];
    }
    private function issues(?string $json): array
    {
        if (!$json) { return []; }
        try { $data = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $json), true, 32, JSON_THROW_ON_ERROR); }
        catch (\Throwable) { $this->invalid('El archivo errors.json no es válido.'); }
        if (!is_array($data)) { $this->invalid('El diagnóstico de errores debe ser un objeto JSON.'); }
        $rows = Validator::make($data, ['issues' => 'present|array|max:10000', 'issues.*.severity' => 'required|in:error,warning',
            'issues.*.code' => 'required|string|max:80', 'issues.*.path' => 'present|string|max:2048',
            'issues.*.message' => 'required|string|max:2000'])->validate()['issues'];
        return array_map(fn ($row) => array_intersect_key($row, array_flip(['severity', 'code', 'path', 'message'])), $rows);
    }
    private function invalid(string $message): never { throw ValidationException::withMessages(['inventoryUpload' => $message]); }
}
