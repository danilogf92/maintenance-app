<?php
namespace App\Modules\MaintenancePlans\Queries;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Contracts\Queries\ContractQuery;
use Illuminate\Support\Facades\Gate;
class CalendarQuery
{
    public function execute(int $year, string $search, string $origin='all', ?int $contractId=null, ?array $range=null): array
    {
        if ($contractId) { ContractQuery::scoped()->findOrFail($contractId); }
        [$from,$to]=$range ?? ["$year-01-01","$year-12-31"];
        $plans=PlanQuery::scoped()->with(['equipment','coverage.contract','visits'=>fn($q)=>$q
            ->where(fn($v)=>$v->where(fn($m)=>$m->where('schedule_precision','month')->whereBetween('scheduled_month',[substr($from,0,7),substr($to,0,7)]))
                ->orWhere(fn($d)=>$d->where('schedule_precision','day')->where('scheduled_for','>=',$from)->where('scheduled_for','<',\Carbon\CarbonImmutable::parse($to)->addDay()->format('Y-m-d'))))
            ->with('technician')->withExists(['ticket','completedServices'])->orderBy('scheduled_for')->orderBy('id')])
            ->when($origin==='contract',fn($q)=>$q->whereNotNull('contract_coverage_id'))
            ->when($origin==='equipment',fn($q)=>$q->whereNull('contract_coverage_id'))
            ->when($contractId,fn($q)=>$q->whereHas('coverage',fn($c)=>$c->where('maintenance_contract_id',$contractId)))->get();
        $equipment=Equipment::query()->with('institution')
            ->when(!Gate::allows('workflow.view_all') || $origin!=='all' || $contractId,fn($q)=>$q->whereIn('id',$plans->pluck('equipment_id')))
            ->when($search!=='',fn($q)=>$q->where(fn($e)=>$e->where('name','like',"%$search%")->orWhere('code','like',"%$search%")->orWhere('serial','like',"%$search%")))
            ->orderBy('name')->orderBy('id')->get();
        $visiblePlans=$plans->whereIn('equipment_id',$equipment->pluck('id'));
        $visits=$visiblePlans->flatMap(function($plan) { return $plan->visits->each(fn($v)=>$v->setRelation('plan',$plan)); });
        $tickets=WorkflowPermissions::allows('tickets') ? VisibleRecords::query('tickets')->whereIn('maintenance_visit_id',$visits->pluck('id'))->get()->keyBy('maintenance_visit_id') : collect();
        return ['equipment'=>$equipment,'plansByEquipment'=>$visiblePlans->groupBy('equipment_id'),'tickets'=>$tickets,
            'totalVisits'=>$visits->count(),'agenda'=>$visits->sortBy(fn($v)=>$v->scheduled_for->format('Y-m-d'))->values()];
    }
}
