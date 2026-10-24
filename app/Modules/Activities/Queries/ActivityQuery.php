<?php

namespace App\Modules\Activities\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Support\Cache\PageDataCache;

class ActivityQuery
{
    public function records(string $search, string $filter)
    {
        Permissions::authorize('activities');
        $services = Permissions::allows('services'); $tickets = $services && Permissions::allows('tickets');
        $equipment = $tickets && Permissions::allows('equipment'); $all = auth()->user()->can('workflow.view_all');
        return app(PageDataCache::class)->remember('activities', [$search, $filter, $services, $tickets, $equipment, $all], function () use ($search, $filter, $services, $tickets, $equipment, $all) {
            $query = VisibleRecords::query('activities');
            if ($services) { $query->with(['service' => function ($q) use ($tickets, $equipment, $all) {
                $q->when(!$all, fn ($q) => $q->where('technician_id', auth()->id()))->with('technician');
                if ($tickets) { $q->with(['ticket' => fn ($q) => $q->when(!$all, fn ($q) => $q->where('technician_id', auth()->id()))->when($equipment, fn ($q) => $q->with('equipment'))]); }
            }]); }
            $query->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%')->orWhere('result', 'like', '%'.$search.'%')));
            if ($filter === 'with_result') { $query->whereNotNull('result')->where('result', '<>', ''); }
            if ($filter === 'pending_result') { $query->where(fn ($q) => $q->whereNull('result')->orWhere('result', '')); }
            if ($filter === 'with_photos') { $query->whereJsonLength('photos', '>', 0); }
            return $query->latest('created_at')->orderByDesc('id')->get()->each(fn ($record) => $record->setAttribute('photo_count', count($record->photos ?? [])));
        }, config('page-cache.records_seconds', 30), auth()->id());
    }
}
