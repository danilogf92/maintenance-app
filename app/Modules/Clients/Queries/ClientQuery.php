<?php

namespace App\Modules\Clients\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Support\Cache\PageDataCache;

class ClientQuery
{
    public function records(string $search, string $active)
    {
        Permissions::authorize('clients');
        $equipment = Permissions::allows('equipment');
        return app(PageDataCache::class)->remember('clients', [$search, $active, $equipment], function () use ($search, $active, $equipment) {
            $query = VisibleRecords::query('clients');
            if ($equipment) { $query->withCount('equipment'); }
            return $query->when($search !== '', fn ($q) => $q->where(fn ($match) => $match
                ->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')->orWhere('tax_id', 'like', '%'.$search.'%')))
                ->when($active !== '', fn ($q) => $q->where('active', $active === '1'))->orderBy('name')->orderBy('id')->get();
        }, config('page-cache.records_seconds', 30), auth()->id());
    }
}
