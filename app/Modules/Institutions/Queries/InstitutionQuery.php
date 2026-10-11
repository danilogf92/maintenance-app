<?php

namespace App\Modules\Institutions\Queries;

use App\Models\Institution;
use App\Support\Cache\PageDataCache;

class InstitutionQuery
{
    public function records(int $userId, string $search, string $type)
    {
        return app(PageDataCache::class)->remember('institutions', [$search, $type],
            fn () => $this->build($search, $type)->get(), config('page-cache.records_seconds', 30), $userId);
    }

    public function options()
    {
        return app(PageDataCache::class)->remember('institution-options', [],
            fn () => Institution::orderBy('name')->get(['id', 'name']), config('page-cache.settings_seconds', 300));
    }

    public function build(string $search, string $type)
    {
        return Institution::query()->withCount('users')
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderBy('name')->orderBy('id');
    }
}
