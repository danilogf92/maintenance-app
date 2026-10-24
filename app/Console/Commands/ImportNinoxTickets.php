<?php
namespace App\Console\Commands;
use App\Modules\Tickets\Import\{TicketSource,ImportTicket,LinkNinoxServices};
use Illuminate\Console\Command;
class ImportNinoxTickets extends Command
{
    protected $aliases = ['data:load-tickets'];
    protected $signature='ninox:import-tickets {--source=} {--dry-run}';
    protected $description='Importa tickets históricos y vincula bitácoras usando referencias Ninox recíprocas.';
    public function handle(): int
    {
        $lock=fopen(storage_path('framework/ninox-ticket-import.lock'),'c');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) { $this->error('Ya hay una importación de tickets en ejecución.'); return self::FAILURE; }
        try { return $this->import(); }
        catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
        finally { flock($lock,LOCK_UN); fclose($lock); }
    }
    private function import(): int
    {
        $path=$this->option('source') ?: base_path('database/imports/ninox-tickets/export/records.jsonl');
        $records=app(TicketSource::class)->read($path);
        if ($this->option('dry-run')) { $this->info(count($records).' tickets validados. No se escribieron datos.'); return self::SUCCESS; }
        $counts=['created'=>0,'existing'=>0,'conflicts'=>0];
        foreach ($records as $record) { $counts[app(ImportTicket::class)->execute($record)]++; }
        $links=app(LinkNinoxServices::class)->execute();
        $this->table(['Resultado','Cantidad'],collect([...$counts,...$links])->map(fn($n,$k)=>[$k,$n])->values()->all());
        $this->info('Tickets → Detalles. Los equipos se vinculan con data:load-equipment. Técnicos y contactos se completan con sus archivos reales.');
        return $counts['conflicts'] || $links['link_conflicts'] ? self::FAILURE : self::SUCCESS;
    }
}
