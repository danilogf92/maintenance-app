<?php

namespace App\Modules\Contracts\Queries;

use App\Modules\MaintenancePlans\Configuration\Schedule;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use Illuminate\Support\Facades\Gate;

class Compliance
{
    public function forContract(int $id)
    {
        $contract = ContractQuery::scoped()->findOrFail($id);
        $visible = PlanQuery::scoped()->select('id');

        return $contract->coverages()->when(! Gate::allows('workflow.view_all') && $contract->created_by !== auth()->id(), fn ($q) => $q->whereHas('plans', fn ($p) => $p->whereIn('id', $visible)))->with(['equipment', 'plans' => fn ($q) => $q->whereIn('id', $visible)
            ->withCount(['visits as programmed_count' => fn ($v) => $v->where('status', '!=', 'cancelled'),
                'visits as completed_count' => fn ($v) => $v->where('status', '!=', 'cancelled')->whereHas('completedServices'),
                'visits as cancelled_count' => fn ($v) => $v->where('status', 'cancelled'),
                'visits as overdue_count' => fn ($v) => $v->where('status', '!=', 'cancelled')->whereDoesntHave('completedServices')
                    ->where(fn ($d) => $d->where(fn ($m) => $m->where('schedule_precision', 'month')->where('scheduled_month', '<', today()->format('Y-m')))
                        ->orWhere(fn ($day) => $day->where('schedule_precision', 'day')->whereDate('scheduled_for', '<', today())))])])->get()
            ->each(function ($coverage) {
                $coverage->committed = $coverage->plans->sum(fn ($p) => $p->expected_visits ?? Schedule::capacity($p) ?? 0);
                $coverage->programmed = $coverage->plans->sum('programmed_count');
                $coverage->completed = $coverage->plans->sum('completed_count');
                $coverage->overdue = $coverage->plans->sum('overdue_count');
                $coverage->cancelled = $coverage->plans->sum('cancelled_count');
            });
    }
}
