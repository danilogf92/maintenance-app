<?php
namespace App\Modules\DataManagement\Support;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class CatalogPackage
{
    protected function inbox(): string { return 'data-management/inbox'; }
    protected function definitions(): array { return ['clients' => 'A', 'institutions' => 'B']; }
    protected function prepareCommand(): string { return 'PrepararClientes.cmd'; }
    public function latest(bool $verify = true): ?array
    {
        $disk = Storage::disk('local'); $packages = [];
        foreach ($disk->directories($this->inbox()) as $directory) {
            if (!preg_match('/^[a-f0-9]{32}$/D', basename($directory)) || !$disk->exists($directory.'/package.json')) { continue; }
            $path = $disk->path($directory.'/package.json');
            $metadata = is_file($path) && filesize($path) <= 65536 ? json_decode(file_get_contents($path), true) : null;
            $packages[] = ['directory' => $directory, 'stamp' => is_string($metadata['created_at'] ?? null) ? $metadata['created_at'] : gmdate('Y-m-d\TH:i:s', $disk->lastModified($directory.'/package.json'))];
        }
        usort($packages, fn ($a, $b) => strcmp($b['stamp'], $a['stamp']));
        return $packages ? $this->read($packages[0]['directory'], $verify) : null;
    }
    public function read(string $directory, bool $verify = true): array
    {
        $disk = Storage::disk('local'); $path = $disk->path($directory.'/package.json');
        $this->assertFile($directory, 'package.json', 65536);
        try { $manifest = json_decode(file_get_contents($path), true, 32, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { $this->invalid('El manifiesto del paquete no es válido. Ejecuta '.$this->prepareCommand().' de nuevo.'); }
        if (($manifest['version'] ?? null) !== 1 || !is_string($manifest['id'] ?? null) || !preg_match('/^[a-f0-9]{32}$/D', $manifest['id']) || !is_array($manifest['tables'] ?? null)) {
            $this->invalid('El paquete no tiene el formato esperado.');
        }
        $tables = [];
        foreach ($this->definitions() as $entity => $table) {
            $entry = $manifest['tables'][$entity] ?? null;
            if (!is_array($entry) || ($entry['source_table'] ?? null) !== $table || !is_string($entry['source_name'] ?? null)
                || !is_int($entry['records'] ?? null) || $entry['records'] < 1 || $entry['records'] > 50000
                || !is_string($entry['sha256'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $entry['sha256'])) { $this->invalid('Falta una tabla válida en el paquete: '.$entity.'.'); }
            $this->assertFile($directory, $entity.'.jsonl', 50 * 1024 * 1024);
            if ($verify && !hash_equals($entry['sha256'], hash_file('sha256', $disk->path($directory.'/'.$entity.'.jsonl')))) { $this->invalid('Los archivos preparados han cambiado. Ejecuta '.$this->prepareCommand().' de nuevo.'); }
            $tables[$entity] = $entry;
        }
        return ['id' => $manifest['id'], 'directory' => $directory, 'tables' => $tables];
    }
    public function rows(array $package): array
    {
        $result = [];
        foreach ($package['tables'] as $entity => $entry) {
            $rows = []; $handle = fopen(Storage::disk('local')->path($package['directory'].'/'.$entity.'.jsonl'), 'rb');
            try {
                while (($line = fgets($handle, 2 * 1024 * 1024)) !== false) {
                    if (trim($line) === '') { continue; }
                    try { $row = json_decode($line, true, 64, JSON_THROW_ON_ERROR); }
                    catch (\JsonException) { $this->invalid('Hay un registro JSON inválido en '.$entity.'. No se ha iniciado la carga.'); }
                    $id = $row['ninox_id'] ?? null;
                    if (!is_array($row) || ($row['ninox_table'] ?? null) !== $entry['source_table'] || (!is_string($id) && !is_int($id))
                        || !preg_match('/^[1-9][0-9]{0,17}$/D', (string)$id) || isset($rows[(string)$id])) { $this->invalid('Identidad inválida o duplicada en '.$entity.'.'); }
                    $rows[(string)$id] = $row;
                    if (count($rows) > 50000) { $this->invalid('El paquete excede el límite de validación.'); }
                }
            } finally { fclose($handle); }
            if (count($rows) !== $entry['records']) { $this->invalid('La cantidad de registros no coincide en '.$entity.'.'); }
            $result[$entity] = $rows;
        }
        return $result;
    }
    private function assertFile(string $directory, string $name, int $limit): void
    {
        $root = realpath(Storage::disk('local')->path($directory)); $path = Storage::disk('local')->path($directory.'/'.$name);
        if (!$root || is_link($path) || !is_file($path) || !str_starts_with((string)realpath($path), $root.DIRECTORY_SEPARATOR) || filesize($path) > $limit) {
            $this->invalid('Archivo de preparación ausente, demasiado grande o fuera de la carpeta permitida.');
        }
    }
    public function invalid(string $message): never { throw ValidationException::withMessages(['catalogImport' => $message]); }
}
