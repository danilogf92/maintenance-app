<?php
namespace App\Modules\MaintenancePlans\Queries;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use Illuminate\Support\Facades\Gate;
class PlanQuery
{
    public static function scoped()
    {
        Permissions::authorize(); $query = MaintenancePlan::query();
        return $query->when(!Gate::allows('workflow.view_all'), fn ($q) => $q->where(fn ($p) => $p->where('created_by',auth()->id())->orWhere('technician_id', auth()->id())
            ->orWhereHas('visits', fn ($v) => $v->where('technician_id', auth()->id()))));
    }
    public function records(string $search, string $active)
    {
        return self::scoped()->with(['equipment', 'technician', 'coverage.contract'])->withCount('visits')
            ->when($search !== '', fn ($q) => $q->where(fn ($p) => $p->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')
                ->orWhereHas('equipment', fn ($e) => $e->where('name', 'like', '%'.$search.'%')->orWhere('serial', 'like', '%'.$search.'%'))))
            ->when($active !== '', fn ($q) => $q->where('active', $active === '1'))->orderBy('first_month')->orderBy('id')->get()->each(fn($plan)=>$plan->setAttribute('planning_origin',$plan->contract_coverage_id ? 'Contractual' : 'Independiente por equipo'));
    }
}
