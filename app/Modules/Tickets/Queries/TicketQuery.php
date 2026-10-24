<?php

namespace App\Modules\Tickets\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Support\Cache\PageDataCache;
use Illuminate\Support\Facades\Gate;

class TicketQuery
{
    public function records(string $search, string $status)
    {
        Permissions::authorize('tickets');
        $equipment = Permissions::allows('equipment'); $services = Permissions::allows('services'); $all = Gate::allows('workflow.view_all');
        $engineers = \App\Modules\FieldEngineers\Configuration\Permissions::allows();
        return app(PageDataCache::class)->remember('tickets', [$search, $status, $equipment, $services, $all, $engineers], function () use ($search, $status, $equipment, $services, $all, $engineers) {
            $query = VisibleRecords::query('tickets')->with('technician');
            if ($engineers) { $query->with(['fieldEngineers'=>fn($q)=>$q->select(['field_engineers.id','name'])]); }
            if ($equipment) { $query->with('equipment'); }
            if ($services) { $query->withCount(['services as visible_services_count' => fn ($q) => $q->when(! $all, fn ($q) => $q->where('technician_id', auth()->id()))]); }
            $items = $query->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$search.'%')->orWhere('subject', 'like', '%'.$search.'%')))
                ->when($status !== '', fn ($q) => $q->where('status', $status))->latest('opened_at')->orderBy('id')->get();
            foreach ($items as $item) { $item->setAttribute('technician_label', $item->technician?->name ?: ($engineers ? $item->fieldEngineers->pluck('name')->unique()->implode(', ') : '')); }
            return $items;
        }, config('page-cache.records_seconds', 30), auth()->id());
    }
}
