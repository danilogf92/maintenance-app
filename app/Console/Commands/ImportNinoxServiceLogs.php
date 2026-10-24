<?php
namespace App\Console\Commands;
use App\Modules\Services\Import\{SourceRecords, ApprovedMappings, ImportServiceLogs};
use Illuminate\Console\Command;
class ImportNinoxServiceLogs extends Command
{
    protected $aliases = ['data:load-services'];
    protected $signature = 'ninox:import-service-logs {--source=} {--files-root=} {--map=} {--dry-run}';
    protected $description = 'Importa bitácoras históricas de Ninox sin inventar relaciones ni duplicar registros.';
    public function handle(): int
    {
        $lockPath = storage_path('framework/ninox-service-import.lock');
        $lock = fopen($lockPath, 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { $this->error('Ya hay una importación en ejecución.'); return self::FAILURE; }
        try { return $this->runImport(); }
        catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }
    private function runImport(): int
    {
        $base = base_path('database/imports/ninox-service-logs');
        $records = app(SourceRecords::class)->read($this->option('source') ?: $base.'/export/records.jsonl');
        $map = app(ApprovedMappings::class)->read($this->option('map'));
        if ($this->option('dry-run')) { $this->info(count($records).' bitácoras validadas. No se escribieron datos ni archivos.'); return self::SUCCESS; }
        $roots = array_filter([$this->option('files-root'), storage_path('app/private/ninox-inbox'), $base]);
        $result = app(ImportServiceLogs::class)->execute($records, $map, $roots);
        $this->table(['Resultado', 'Cantidad'], collect($result)->map(fn ($v, $k) => [$k, $v])->values()->all());
        file_put_contents(storage_path('app/private/ninox-service-import-summary.json'), json_encode(['finished_at' => now()->toIso8601String(), ...$result], JSON_PRETTY_PRINT));
        $this->info('Consulta Servicios → Detalles. Las relaciones pendientes no se asignan automáticamente.');
        return $result['conflicts'] || $result['invalid'] ? self::FAILURE : self::SUCCESS;
    }
}
