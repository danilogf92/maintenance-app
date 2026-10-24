<?php
namespace App\Console\Commands;
use App\Modules\MaintenanceProtocols\Import\{ProtocolSource, ImportProtocols};
use Illuminate\Console\Command;
class LoadMaintenanceProtocols extends Command
{
    protected $signature = 'data:load-protocols {--source=} {--dry-run}';
    protected $description = 'Carga protocolos, pasos y adjuntos recibidos, sin inventar relaciones ni sobrescribir cambios.';
    public function handle(): int
    {
        $lock = fopen(storage_path('framework/maintenance-protocols-import.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { $this->error('Otra carga de protocolos está en ejecución.'); return self::FAILURE; }
        try {
            $directory = $this->option('source') ?: base_path('database/imports/maintenance-protocols');
            $source = app(ProtocolSource::class)->read($directory);
            if ($this->option('dry-run')) {
                $this->info(count($source['protocols']).' protocolos, '.count($source['steps']).' pasos y '.count($source['files']).' adjuntos validados.');
            } else {
                $counts = app(ImportProtocols::class)->execute($source, $directory);
                $this->table(['Resultado', 'Cantidad'], collect($counts)->map(fn ($n, $key) => [$key, $n])->values()->all());
                $this->info('Servicio técnico → Protocolos. Los pasos sin padre permanecen en Pendientes.');
            }
            return self::SUCCESS;
        } catch (\Throwable $exception) { $this->error($exception->getMessage()); return self::FAILURE; }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }
}
