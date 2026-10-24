<?php
namespace App\Modules\DataManagement\Support;
use App\Modules\DataManagement\Actions\ReadInventory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class InventoryStore
{
    private const PATH = 'data-management/current/inventory.json';
    public function current(): ?array
    {
        $disk = Storage::disk('local');
        return $disk->exists(self::PATH) ? json_decode($disk->get(self::PATH), true, 64, JSON_THROW_ON_ERROR) : null;
    }
    public function save(array $data): void
    {
        if (!Storage::disk('local')->put(self::PATH, json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))) {
            throw ValidationException::withMessages(['inventoryUpload' => 'No se pudo guardar el diagnóstico privado.']);
        }
    }
    public function latestPath(): ?string
    {
        $root = realpath(config('data-management.inventory_root')); if (!$root) { return null; }
        $paths = [];
        foreach (glob($root.'/*/carpetas.json') ?: [] as $path) {
            $actual = realpath($path);
            if ($actual && str_starts_with($actual, $root.DIRECTORY_SEPARATOR) && is_file($actual)) { $paths[] = $actual; }
        }
        usort($paths, fn ($a, $b) => (filemtime($b) <=> filemtime($a)) ?: strcmp($b, $a));
        return $paths[0] ?? null;
    }
    public function loadLatest(): array
    {
        $path = $this->latestPath();
        if (!$path) { throw ValidationException::withMessages(['inventoryUpload' => 'Ejecuta data/LeerDatos.cmd o sube el ZIP de diagnóstico.']); }
        $data = app(ReadInventory::class)->execute($path); $this->save($data); return $data;
    }
}
