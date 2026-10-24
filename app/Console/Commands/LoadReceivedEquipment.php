<?php
namespace App\Console\Commands;
use App\Modules\Equipment\Import\{EquipmentSource,ImportEquipment,LinkEquipmentTickets,ConfirmCatalogLinks};
use App\Support\Cache\PageDataCache;
use Illuminate\Console\Command;
class LoadReceivedEquipment extends Command
{
    protected $signature='data:load-equipment {--source=} {--dry-run}';
    protected $description='Carga equipos recibidos y vincula tickets con referencias verificadas.';
    public function handle(): int
    {
        $lock=fopen(storage_path('framework/equipment-import.lock'),'c');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) { $this->error('Ya hay una carga de equipos en ejecución.'); return self::FAILURE; }
        try { return $this->loadEquipment(); }
        catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
        finally { flock($lock,LOCK_UN); fclose($lock); }
    }
    private function loadEquipment(): int
    {
        $rows=app(EquipmentSource::class)->read($this->option('source') ?: base_path('database/imports/equipment/export/records.jsonl'));
        if ($this->option('dry-run')) { $this->info(count($rows).' equipos validados, sin escribir.'); return self::SUCCESS; }
        $counts=['created'=>0,'existing'=>0,'conflicts'=>0];
        foreach ($rows as $row) { $counts[app(ImportEquipment::class)->execute($row)]++; }
        app(ConfirmCatalogLinks::class)->execute(); $links=app(LinkEquipmentTickets::class)->execute();
        app(PageDataCache::class)->invalidate('equipment','tickets','services','equipment_models');
        $this->table(['Resultado','Cantidad'],collect([...$counts,...$links])->map(fn($n,$k)=>[$k,is_array($n)?count($n):$n])->values()->all());
        if ($links['ticket_conflicts']) { $this->warn('Tickets con referencias contradictorias: '.implode(', ',$links['ticket_conflicts']).'. Permanecen pendientes.'); }
        $this->info('Equipos → Detalles. Datos por completar muestra las relaciones pendientes. No se crean planes ni entidades ficticias.');
        return $counts['conflicts'] ? self::FAILURE : self::SUCCESS;
    }
}
