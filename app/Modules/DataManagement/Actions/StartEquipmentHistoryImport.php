<?php
namespace App\Modules\DataManagement\Actions;
use App\Modules\DataManagement\Jobs\ImportEquipmentHistoryPackage;
use App\Modules\DataManagement\Support\{CatalogPackage, EquipmentHistoryPackage, MappingStore, SourceName};
class StartEquipmentHistoryImport extends StartCatalogImport
{
    protected function reader(): CatalogPackage { return app(EquipmentHistoryPackage::class); }
    protected function operation(): string { return 'histories'; }
    protected function dispatch(string $id): void { ImportEquipmentHistoryPackage::dispatch($id)->afterCommit(); }
    protected function configureMappings(array $package): void
    {
        $store = app(MappingStore::class); $changes = []; $sources = []; $version = $store->fingerprint(); $existing = $store->read();
        foreach ($package['tables'] as $entity => $table) {
            $source = ['name' => $table['source_name'], 'folder' => $entity];
            if ($store->resolve($source['name'], $existing) !== null) { continue; }
            $sources[] = $source; $changes[SourceName::key($source)] = $entity;
        }
        if ($changes) { $store->save($changes, $sources, $version); }
    }
}
