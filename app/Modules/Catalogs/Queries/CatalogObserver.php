<?php
namespace App\Modules\Catalogs\Queries;

class CatalogObserver
{
    public function saved($record): void
    {
        app(\App\Support\Cache\PageDataCache::class)->invalidate('equipment');
        if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
            \Illuminate\Support\Facades\DB::afterCommit(fn () => app(\App\Support\Cache\PageDataCache::class)->invalidate('equipment'));
        }
    }
}
