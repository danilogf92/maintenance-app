<?php

namespace App\Modules\Services\Queries;

use App\Support\Cache\PageDataCache;

class ServiceCacheObserver
{
    public function saved(): void { app(PageDataCache::class)->invalidate('services'); }
    public function deleted(): void { app(PageDataCache::class)->invalidate('services'); }
}
