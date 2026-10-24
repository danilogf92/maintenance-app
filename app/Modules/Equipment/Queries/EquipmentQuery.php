<?php

namespace App\Modules\Equipment\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Support\Cache\PageDataCache;
use Illuminate\Support\Facades\Gate;

class EquipmentQuery
{
    public function records(string $search, string $status)
    {
        Permissions::authorize('equipment');
        $clients = Permissions::allows('clients'); $tickets = Permissions::allows('tickets'); $all = Gate::allows('workflow.view_all');
        return app(PageDataCache::class)->remember('equipment', [$search, $status, $clients, $tickets, $all], function () use ($search, $status, $clients, $tickets, $all) {
            $query = VisibleRecords::query('equipment')->with('institution');
            if ($clients) { $query->with('client'); }
            if ($tickets) { $query->withCount(['tickets as visible_tickets_count' => fn ($q) => $q->when(! $all, fn ($q) => $q->where('technician_id', auth()->id()))]); }
            return $query->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('name', 'like', '%'.$search.'%')
                ->orWhere('code', 'like', '%'.$search.'%')->orWhere('serial', 'like', '%'.$search.'%')->orWhere('brand', 'like', '%'.$search.'%')->orWhere('model', 'like', '%'.$search.'%')))
                ->when($status !== '', fn ($q) => $q->where('status', $status))->orderBy('name')->orderBy('id')->get();
        }, config('page-cache.records_seconds', 30), auth()->id());
    }
}
