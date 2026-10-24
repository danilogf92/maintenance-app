<?php
namespace App\Modules\DataManagement\Queries;
use App\Modules\DataManagement\Configuration\{Entities, SourceHints};
use App\Modules\DataManagement\Support\{MappingStore, SourceName};
class Reconciliation
{
    public function rows(array $inventory, array $edits): array
    {
        $schema = app(SchemaReview::class); $metadata = $schema->metadata();
        $store = app(MappingStore::class); $mappings = $store->read(); $rows = [];
        $desired = array_map(fn ($source) => $edits[SourceName::key($source)] ?? $store->resolve($source['name'], $mappings), $inventory['tables']);
        $counts = $schema->counts(array_values(array_filter($desired, 'is_string')));
        foreach ($inventory['tables'] as $source) {
            $key = SourceName::key($source); $configured = $store->resolve($source['name'], $mappings);
            $target = array_key_exists($key, $edits) ? ($edits[$key] ?: null) : $configured;
            $target = is_string($target) && $target !== '' ? $target : null;
            $hint = SourceHints::for($source); $implemented = $target && isset($metadata[$target]);
            $state = $implemented ? 'Destino existente · por validar' : ($target ? 'Destino no disponible' : 'Sin correspondencia');
            if ($source['records_invalid'] > 0 || $source['status'] === 'fallido' || $source['status'] === 'con_errores') { $state = 'Errores en el origen'; }
            $rows[] = ['key' => $key, 'source' => $source, 'target' => $target,
                'target_label' => $target ? Entities::label($target) : 'Pendiente', 'implemented' => $implemented,
                'database_count' => $implemented ? $counts[$target] : null, 'state' => $state, 'hint' => $hint,
                'columns' => $implemented ? $metadata[$target]['columns'] : [], 'foreign_keys' => $implemented ? $metadata[$target]['foreign_keys'] : []];
        }
        return $rows;
    }
}
