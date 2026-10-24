<?php
namespace App\Modules\DataManagement\Support;
use App\Modules\DataManagement\Configuration\Entities;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\{Rule, ValidationException};
class MappingStore
{
    public function path(): string { return config('data-management.mapping_path'); }
    public function read(): array
    {
        $path = is_file($this->path()) ? $this->path() : config('data-management.mapping_template');
        try { $data = json_decode(file_get_contents($path), true, 32, JSON_THROW_ON_ERROR); }
        catch (\Throwable) { $this->invalid('No se pudo leer data/correspondencias.json.'); }
        if (!is_array($data) || array_is_list($data) && $data !== []) { $this->invalid('El archivo debe contener un objeto de correspondencias.'); }
        foreach ($data as $name => $target) {
            if (!is_string($name) || mb_strlen($name) > 255 || !is_string($target) || !preg_match('/^[a-z][a-z0-9_]{0,100}$/', $target)) {
                $this->invalid('El archivo contiene una correspondencia inválida.');
            }
        }
        return $data;
    }
    public function fingerprint(): string { return hash('sha256', json_encode($this->read(), JSON_THROW_ON_ERROR)); }
    public function resolve(string $name, ?array $mappings = null): ?string
    {
        $normalized = SourceName::normalize($name);
        foreach ($mappings ?? $this->read() as $source => $target) { if (SourceName::normalize($source) === $normalized) { return $target; } }
        return null;
    }
    public function save(array $changes, array $sources, string $fingerprint): void
    {
        Validator::make(['mappings' => $changes], ['mappings' => 'array|max:1000', 'mappings.*' => ['nullable', 'string', Rule::in(['', ...array_keys(Entities::all())])]])->validate();
        $names = []; foreach ($sources as $source) { $names[SourceName::key($source)] = $source['name']; }
        if (array_diff_key($changes, $names)) { $this->invalid('La selección incluye una tabla ajena al inventario.'); }
        $grouped = [];
        foreach ($changes as $key => $target) {
            $normalized = SourceName::normalize($names[$key]);
            if (isset($grouped[$normalized]) && $grouped[$normalized]['target'] !== ($target ?: '')) { $this->invalid('Una misma tabla de origen tiene destinos contradictorios.'); }
            $grouped[$normalized] = ['name' => $names[$key], 'target' => $target ?: ''];
        }
        $lock = fopen(storage_path('framework/data-management-mappings.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { $this->invalid('Hay otra actualización de correspondencias en curso.'); }
        try {
            if ($fingerprint !== $this->fingerprint()) { $this->invalid('Otro usuario modificó las correspondencias. Recarga antes de guardar.'); }
            $data = $this->read();
            foreach ($grouped as $normalized => $change) {
                foreach (array_keys($data) as $existing) { if (SourceName::normalize($existing) === $normalized) { unset($data[$existing]); } }
                if ($change['target'] !== '') { $data[$change['name']] = $change['target']; }
            }
            app('files')->replace($this->path(), json_encode((object)$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
        } catch (ValidationException $e) { throw $e; }
        catch (\Throwable) { $this->invalid('No se pudo escribir data/correspondencias.json. Revisa los permisos de la carpeta.'); }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }
    private function invalid(string $message): never { throw ValidationException::withMessages(['mappings' => $message]); }
}
