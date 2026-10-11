<?php

namespace App\Console\Commands;

use App\Support\Cache\PageDataCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearPageCache extends Command
{
    protected $signature = 'maintenance:cache-clear';

    protected $description = 'Renueva la caché de páginas sin borrar sesiones ni permisos';

    public function handle(): int
    {
        if (config('page-cache.store') === 'page-data') {
            Cache::store('page-data')->flush();
        } else {
            app(PageDataCache::class)->invalidate('users', 'institutions', 'institution-options', 'brand', 'palette');
        }
        $this->info('Caché de páginas renovada.');

        return self::SUCCESS;
    }
}
