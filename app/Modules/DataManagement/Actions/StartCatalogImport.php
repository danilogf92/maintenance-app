<?php
namespace App\Modules\DataManagement\Actions;
use App\Modules\DataManagement\Configuration\Permissions;
use App\Modules\DataManagement\Jobs\ImportCatalogPackage;
use App\Modules\DataManagement\Models\DataImportRun;
use App\Modules\DataManagement\Support\{CatalogPackage, MappingStore};
use Illuminate\Support\Facades\{Cache, DB, Storage};
use Illuminate\Support\Str;
class StartCatalogImport
{
    protected function reader(): CatalogPackage { return app(CatalogPackage::class); }
    protected function operation(): string { return 'catalogs'; }
    protected function configureMappings(array $package): void {}
    protected function dispatch(string $id): void { ImportCatalogPackage::dispatch($id)->afterCommit(); }
    public function execute(): DataImportRun
    {
        Permissions::authorize('manage');
        return Cache::lock('data-management-start-catalog', 30)->block(3, function () {
            $reader = $this->reader(); $package = $reader->latest();
            if (!$package) { $reader->invalid('No hay un paquete preparado para esta carga. Ejecuta '.($this->operation()==='histories' ? 'data/PrepararHistoriales.cmd' : 'data/PrepararClientes.cmd').'.'); }
            if (DataImportRun::whereIn('status', ['queued', 'processing'])->exists()) { $reader->invalid('Ya hay una carga pendiente o en ejecución. Espera su resultado.'); }
            $this->configureMappings($package); $store = app(MappingStore::class); $mappings = $store->read();
            foreach ($package['tables'] as $entity => $table) {
                if ($store->resolve($table['source_name'], $mappings) !== $entity) { $reader->invalid('Revisa y guarda la correspondencia de '.$table['source_name'].' → '.$entity.'.'); }
            }
            $id = (string)Str::uuid(); $destination = 'data-management/imports/'.$id;
            $disk = Storage::disk('local'); $disk->makeDirectory($destination);
            foreach (['package.json', ...array_map(fn ($entity) => $entity.'.jsonl', array_keys($package['tables']))] as $name) {
                if (!$disk->copy($package['directory'].'/'.$name, $destination.'/'.$name)) { $reader->invalid('No se pudo guardar la copia de trabajo de la carga.'); }
            }
            // Freeze and validate the exact input before scheduling; external Windows paths never enter Docker.
            try { $snapshot = $reader->read($destination); $reader->rows($snapshot); }
            catch (\Illuminate\Validation\ValidationException $e) {
                $failed = DataImportRun::create(['id' => $id, 'operation' => $this->operation(), 'status' => 'failed', 'requested_by' => auth()->id(), 'package_id' => $package['id'],
                    'summary' => [], 'finished_at' => now(), 'issues' => [['severity' => 'error', 'code' => 'package_invalid', 'message' => $e->validator->errors()->first()]]]);
                app(CatalogImportDiagnostics::class)->writeResult($failed);
                throw $e;
            }
            $run = DB::transaction(function () use ($id, $package) {
                $run = DataImportRun::create(['id' => $id, 'operation' => $this->operation(), 'status' => 'queued', 'requested_by' => auth()->id(), 'package_id' => $package['id'], 'summary' => [], 'issues' => []]);
                $this->dispatch($id);
                return $run;
            });
            return $run;
        });
    }
}
