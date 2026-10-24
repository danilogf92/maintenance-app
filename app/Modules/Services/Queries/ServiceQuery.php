<?php

namespace App\Modules\Services\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Support\Cache\PageDataCache;

class ServiceQuery
{
    public function records(string $search, string $status)
    {
        Permissions::authorize('services');
        $tickets = Permissions::allows('tickets'); $equipment = $tickets && Permissions::allows('equipment');
        $activities = Permissions::allows('activities'); $reports = Permissions::allows('technical_reports');
        $all = auth()->user()->can('workflow.view_all');
        $engineers = \App\Modules\FieldEngineers\Configuration\Permissions::allows();
        return app(PageDataCache::class)->remember('services', [$search, $status, $tickets, $equipment, $activities, $reports, $all, $engineers], function () use ($search, $status, $tickets, $equipment, $activities, $reports, $all, $engineers) {
            $query = VisibleRecords::query('services')->with('technician')->withExists('ninoxImport');
            if ($engineers) { $query->with('fieldEngineer:id,name'); }
            if ($tickets) { $query->with(['ticket' => fn ($q) => $q->when(!$all, fn ($q) => $q->where('technician_id', auth()->id()))->when($equipment, fn ($q) => $q->with('equipment'))]); }
            if ($activities) { $query->withCount('activities'); }
            if ($reports) { $query->withCount('reports'); }
            $items = $query->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$search.'%')->orWhere('service_type', 'like', '%'.$search.'%')))
                ->when($status !== '', fn ($q) => $q->where('status', $status))->latest('service_date')->orderBy('id')->get();
            foreach ($items as $item) { $item->setAttribute('technician_label', $item->technician?->name ?: ($engineers ? ($item->fieldEngineer?->name ?? '') : '')); }
            return $items;
        }, config('page-cache.records_seconds', 30), auth()->id());
    }
}
