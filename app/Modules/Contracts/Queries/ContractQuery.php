<?php

namespace App\Modules\Contracts\Queries;

use App\Modules\Contracts\Configuration\Permissions;
use App\Modules\Contracts\Models\MaintenanceContract;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use Illuminate\Support\Facades\Gate;

class ContractQuery
{
    public static function scoped()
    {
        Permissions::authorize();

        return MaintenanceContract::query()->when(! Gate::allows('workflow.view_all'), fn ($q) => $q->where(fn ($s) => $s->where('created_by', auth()->id())->orWhereHas('coverages.plans', fn ($p) => $p->whereIn('id', PlanQuery::scoped()->select('id')))));
    }

    public function records(string $search, string $active)
    {
        return self::scoped()->with('client')->withCount('coverages')
            ->when($search !== '', fn ($q) => $q->where(fn ($s) => $s->where('number', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")->orWhere('code', 'like', "%$search%")))
            ->when($active !== '', fn ($q) => $q->where('active', $active === '1'))->orderBy('number')->get();
    }
}
