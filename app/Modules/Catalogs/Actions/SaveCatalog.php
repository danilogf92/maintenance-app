<?php
namespace App\Modules\Catalogs\Actions;

use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveCatalog
{
    public function execute(string $catalog, array $input, ?int $id = null)
    {
        Permissions::authorize($catalog, 'manage'); $definition = Catalogs::get($catalog);
        try { return DB::transaction(function () use ($catalog, $input, $id, $definition) {
            $old = $id ? $definition['model']::lockForUpdate()->findOrFail($id) : null;
            $data = Validator::make($input, Catalogs::configuration($catalog, 'Rules')::make())->validate();
            if ($old && $old->lock_version !== (int)$data['lock_version']) {
                throw ValidationException::withMessages(['lock_version' => 'Otro usuario modificó este registro. Recarga antes de guardar.']);
            }
            if ($catalog === 'equipment_models' && array_key_exists('visits_recommended_per_year', $data)) {
                $data['visits_recommended_per_year'] = $data['visits_recommended_per_year'] === '' ? null : $data['visits_recommended_per_year'];
            }
            $data['name'] = trim($data['name']);
            if ($data['name'] === '') { throw ValidationException::withMessages(['name' => 'Escribe un nombre.']); }
            $data['name_key'] = Catalogs::key($data['name'], $catalog === 'equipment_models' ? (int)($data['brand_id'] ?? 0) ?: null : null);
            if (Validator::make($data, ['name_key' => [Rule::unique($catalog)->ignore($id)]])->fails()) {
                throw ValidationException::withMessages(['name' => 'Ya existe ese nombre en este catálogo.']);
            }
            app(ValidateCatalogParent::class)->execute($catalog, $data, $old);
            $record = $old ?? new $definition['model'];
            if (!$old) { $record->code = $definition['prefix'].'-'.Str::upper(Str::random(8)); $record->created_by = auth()->id(); }
            $record->fill($data); $record->lock_version = ($old?->lock_version ?? 0) + 1; $record->save();
            if ($catalog !== 'manufacturers') { app(SyncEquipmentNames::class)->execute($catalog, $record); }
            return $record->fresh();
        }); } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['name' => 'Ya existe ese nombre en este catálogo.']);
        }
    }
}
