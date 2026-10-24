<?php

namespace App\Modules\Activities\Queries;

use App\Support\Cache\PageDataCache;

class ActivityCacheObserver
{
    public function saved(): void { app(PageDataCache::class)->invalidate('activities'); }
    public function deleted(): void { app(PageDataCache::class)->invalidate('activities'); }
}
