<?php

namespace App\Modules\Equipment\Queries;

use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Model;

class EquipmentCacheObserver
{
    public function saved(Model $model): void { app(PageDataCache::class)->invalidate('equipment'); }
    public function deleted(Model $model): void { app(PageDataCache::class)->invalidate('equipment'); }
}
