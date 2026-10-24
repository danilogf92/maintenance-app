<?php
namespace App\Modules\DataManagement\Actions;
use App\Modules\DataManagement\Support\MappingStore;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ReviewDiagnostics
{
    public function create(array $inventory, array $rows, array $relations): string
    {
        $id = now()->utc()->format('Ymd-His').'-'.Str::lower(Str::random(8));
        $disk = Storage::disk('local'); $directory = 'data-management/runs/'.$id; $disk->makeDirectory($directory);
        $issues = [];
        foreach ($rows as $row) {
            if (!$row['target']) { $issues[] = ['severity' => 'warning', 'code' => 'mapping_missing', 'path' => $row['source']['folder'], 'message' => 'Falta seleccionar una entidad de destino.']; }
            elseif (!$row['implemented']) { $issues[] = ['severity' => 'error', 'code' => 'target_unavailable', 'path' => $row['source']['folder'], 'message' => 'El destino configurado no está disponible.']; }
            if ($row['source']['records_invalid'] > 0 || in_array($row['source']['status'], ['fallido', 'con_errores'], true)) {
                $issues[] = ['severity' => 'error', 'code' => 'source_errors', 'path' => $row['source']['folder'], 'message' => 'El lector detectó errores en esta tabla.'];
            }
        }
        $errors = count(array_filter($issues, fn ($i) => $i['severity'] === 'error'));
        $payload = ['carpetas.json' => $inventory, 'correspondencias.json' => (object)app(MappingStore::class)->read(),
            'relaciones.json' => $relations,
            'errors.json' => ['issues' => $issues, 'source_issues' => $inventory['issues'] ?? []],
            'status.json' => ['run_id' => $id, 'operation' => 'revision', 'status' => $issues ? 'completado_con_observaciones' : 'completado',
                'errors' => $errors, 'warnings' => count($issues) - $errors, 'database_modified' => false, 'imported_records' => 0],
            'ack.json' => ['run_id' => $id, 'operation' => 'revision', 'imported' => false, 'requires_relationship_validation' => true,
                'tables' => array_map(fn ($r) => ['source' => $r['source']['name'], 'target' => $r['target'], 'status' => $r['state'], 'imported' => false], $rows)]];
        $zipPath = $disk->path($directory.'/diagnostico.zip'); $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) { throw new \RuntimeException('No se pudo crear el diagnóstico.'); }
        try {
            foreach ($payload as $name => $value) {
                $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $disk->put($directory.'/'.$name, $json); $zip->addFromString($name, $json);
            }
        } finally { $zip->close(); }
        return $zipPath;
    }
}
