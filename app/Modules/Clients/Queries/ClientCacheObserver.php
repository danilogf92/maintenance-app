<?php

namespace App\Modules\Clients\Queries;

use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Model;

class ClientCacheObserver
{
    public function saved(Model $model): void { app(PageDataCache::class)->invalidate('clients'); }
    public function deleted(Model $model): void { app(PageDataCache::class)->invalidate('clients'); }
}
