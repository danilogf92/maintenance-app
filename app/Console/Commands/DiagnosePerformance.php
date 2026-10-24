<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiagnosePerformance extends Command
{
    protected $signature = 'maintenance:diagnose';
    protected $description = 'Comprueba versión, conexión y rutas de caché sin mostrar credenciales';

    public function handle(): int
    {
        $manifest = json_decode(file_get_contents(base_path('MANIFEST.json')), true);
        $started = hrtime(true); DB::select('select 1');
        $report = ['version' => $manifest['version'] ?? 'sin manifiesto',
            'database_ms' => round((hrtime(true) - $started) / 1e6, 2),
            'opcache_extension' => extension_loaded('Zend OPcache'),
            'opcache_fpm_configured' => (bool) ini_get('opcache.enable'),
            'config_cached' => app()->configurationIsCached(), 'routes_cached' => app()->routesAreCached(),
            'table_loading' => 'all_authorized_records_without_pagination',
            'runtime_cache' => $this->checkFiles(storage_path('framework/cache')),
            'compiled_views' => $this->checkFiles(storage_path('framework/views'))];
        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(storage_path('logs/performance-check.json'), $json);
        $this->line($json);
        return self::SUCCESS;
    }

    private function checkFiles(string $directory): array
    {
        $file = $directory.'/.diagnostic-'.Str::uuid(); $started = hrtime(true);
        try {
            if (file_put_contents($file, str_repeat('x', 32768)) === false || strlen(file_get_contents($file)) !== 32768) {
                throw new \RuntimeException('No se puede escribir/leer la caché de ejecución.');
            }
            return ['writable' => true, 'write_read_ms' => round((hrtime(true) - $started) / 1e6, 2)];
        } finally { if (is_file($file)) { unlink($file); } }
    }
}
