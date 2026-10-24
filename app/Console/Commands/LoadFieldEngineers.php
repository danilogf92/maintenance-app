<?php
namespace App\Console\Commands;
use App\Modules\FieldEngineers\Import\{EngineerSource,ImportEngineer,LinkEngineerTickets,LinkEngineerServices};
use Illuminate\Console\Command;
class LoadFieldEngineers extends Command
{
    protected $signature='data:load-engineers {--source=} {--dry-run}';
    protected $description='Carga ingenieros de servicio recibidos y verifica vínculos históricos, sin crear cuentas.';
    public function handle(): int
    {
        $lock=fopen(storage_path('framework/field-engineer-import.lock'),'c');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) { $this->error('Ya hay una carga de ingenieros en ejecución.');return self::FAILURE; }
        try { return $this->loadEngineers(); } catch (\Throwable $e) { $this->error($e->getMessage());return self::FAILURE; }
        finally { flock($lock,LOCK_UN);fclose($lock); }
    }
    private function loadEngineers(): int
    {
        $records=app(EngineerSource::class)->read($this->option('source') ?: base_path('database/imports/field-engineers/export/records.jsonl'));
        if ($this->option('dry-run')) { $this->info(count($records).' ingenieros validados, sin escribir.');return self::SUCCESS; }
        $counts=['created'=>0,'existing'=>0,'conflicts'=>0];foreach ($records as $record) { $counts[app(ImportEngineer::class)->execute($record)]++; }
        $tickets=app(LinkEngineerTickets::class)->execute();$services=app(LinkEngineerServices::class)->execute();
        app(\App\Support\Cache\PageDataCache::class)->invalidate('field_engineers','tickets','services');
        $this->table(['Resultado','Cantidad'],collect([...$counts,...$tickets,...$services])->map(fn($n,$k)=>[$k,$n])->values()->all());
        $this->info('Ingenieros de servicio → General / Contacto / Detalles / Relaciones. Las cuentas y sus permisos se conservan.');
        return $counts['conflicts'] || $tickets['ticket_link_conflicts'] || $services['service_link_conflicts'] ? self::FAILURE : self::SUCCESS;
    }
}
