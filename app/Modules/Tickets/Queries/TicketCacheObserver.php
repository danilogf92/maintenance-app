<?php

namespace App\Modules\Tickets\Queries;

use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Model;

class TicketCacheObserver
{
    public function saved(Model $model): void { app(PageDataCache::class)->invalidate('tickets'); }
    public function deleted(Model $model): void { app(PageDataCache::class)->invalidate('tickets'); }
}
