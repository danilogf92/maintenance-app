<?php
namespace App\Console\Commands;
use App\Modules\Catalogs\Import\{CatalogSource, BrandEvidence, ImportCatalogRecord};
use Illuminate\Console\Command;
class ImportNinoxCatalogs extends Command
{
    protected $aliases = ['data:load-catalogs'];
    protected $signature = 'ninox:import-catalogs {--root=} {--dry-run}';
    protected $description = 'Importa marcas y modelos conservando IDs Ninox y evidencia de sus relaciones.';
    public function handle(): int
    {
        $lock = fopen(storage_path('framework/ninox-catalog-import.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { $this->error('Ya hay una importación de catálogos en ejecución.'); return self::FAILURE; }
        try { return $this->import(); }
        catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }
    private function import(): int
    {
        $root = $this->option('root') ?: base_path('database/imports/ninox-catalogs');
        $source = app(CatalogSource::class); $brands = $source->read($root.'/brands', 'brands');
        $models = $source->read($root.'/models', 'equipment_models');
        if ($this->option('dry-run')) { $this->info(count($brands).' marcas y '.count($models).' modelos validados, sin importar.'); return self::SUCCESS; }
        $counts = ['created' => 0, 'existing' => 0, 'conflicts' => 0, 'images_ready' => 0, 'images_missing' => 0,
            'images_invalid' => 0, 'models_linked' => 0, 'models_pending' => 0];
        foreach (['brands' => $brands, 'equipment_models' => $models] as $catalog => $records) {
            foreach ($records as $record) {
                $brand = $catalog === 'equipment_models' ? app(BrandEvidence::class)->candidates($brands, $record) : null;
                $result = app(ImportCatalogRecord::class)->execute($catalog, $record, $brand, $root.'/'.($catalog === 'brands' ? 'brands' : 'models'));
                foreach ($result as $key => $value) { $counts[$key] += $value; }
            }
        }
        $this->table(['Resultado', 'Cantidad'], collect($counts)->map(fn ($n, $key) => [$key, $n])->values()->all());
        $this->info('Catálogos de equipos → Marcas / Modelos → Detalles. Relaciones deducidas pendientes de confirmar con Equipos.');
        return $counts['conflicts'] || $counts['images_invalid'] ? self::FAILURE : self::SUCCESS;
    }
}
