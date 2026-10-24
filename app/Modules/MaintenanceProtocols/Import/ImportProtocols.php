<?php
namespace App\Modules\MaintenanceProtocols\Import;
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolStep, ProtocolFile};
use Illuminate\Support\Facades\{DB, Storage};
class ImportProtocols
{
    public function execute(array $source, string $directory): array
    {
        return DB::transaction(function () use ($source, $directory) {
            $counts = ['protocols' => 0, 'steps' => 0, 'files' => 0, 'pending_steps' => 0]; $protocols = []; $steps = [];
            foreach ($source['protocols'] as $row) {
                $record = $this->record(MaintenanceProtocol::class, $row, ['code' => $row['protocol_code'],
                    'name' => $row['protocol_name'], 'description' => $row['protocol_description'],
                    'version' => $row['protocol_version'], 'type' => $row['protocol_type'],
                    'model_info' => $row['brand_and_model_info'], 'is_obsolete' => $row['is_obsolete']]);
                $protocols[$row['ninox_id']] = $record; $counts['protocols']++;
            }
            foreach ($source['steps'] as $row) {
                $parent = $protocols[$row['protocol_id'] ?? ''] ?? null;
                $position = $parent ? array_search((int)$row['ninox_id'], $source['protocols'][$parent->source_id]['protocol_steps'], true) + 1 : null;
                $record = $this->record(ProtocolStep::class, $row, ['parent_source_id' => $row['protocol_id'],
                    'maintenance_protocol_id' => $parent?->id, 'position' => $position, 'step_number' => $row['step_number'],
                    'instruction' => $row['instruction'], 'report_text' => $row['reports_indication'], 'type' => $row['type'],
                    'range' => $row['range_min_max'], 'include_in_report' => $row['is_add_to_report']]);
                if (!$record->maintenance_protocol_id && $parent) {
                    $record->update(['maintenance_protocol_id' => $parent->id, 'position' => $position]);
                }
                $steps[$row['ninox_id']] = $record; $counts['steps']++; $counts['pending_steps'] += $parent ? 0 : 1;
            }
            foreach ($source['files'] as $file) {
                $path = 'maintenance-protocols/'.$file['path']; $disk = Storage::disk('local');
                if (!$disk->exists($path) || hash_file('sha256', $disk->path($path)) !== $file['sha256']) {
                    $stream = fopen($directory.'/'.$file['path'], 'rb');
                    try { $ok = $disk->put($path, $stream); } finally { fclose($stream); }
                    if (!$ok) { throw new \RuntimeException('No se pudo guardar el adjunto del protocolo.'); }
                }
                ProtocolFile::firstOrCreate(['protocol_step_id' => $steps[$file['step_source_id']]->id, 'sha256' => $file['sha256']],
                    ['name' => $file['name'], 'path' => $path, 'mime' => $file['mime']]); $counts['files']++;
            }
            return $counts;
        });
    }
    private function record(string $model, array $row, array $values)
    {
        $hash = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
        $existing = $model::where('source_id', $row['ninox_id'])->lockForUpdate()->first();
        if ($existing && $existing->source_hash !== $hash) { throw new \RuntimeException('Origen modificado; requiere revisión: '.$row['ninox_id']); }
        return $existing ?? $model::create(['source_id' => $row['ninox_id'], 'source_hash' => $hash, 'source_payload' => $row, ...$values]);
    }
}
