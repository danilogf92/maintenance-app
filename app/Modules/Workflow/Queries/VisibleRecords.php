<?php

namespace App\Modules\Workflow\Queries;

use App\Modules\Workflow\Configuration\Modules;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class VisibleRecords
{
    public static function query(string $module): Builder
    {
        Permissions::authorize($module);
        $query = Modules::get($module)['model']::query();
        if (Gate::allows('workflow.view_all')) { return $query; }

        return match ($module) {
            'tickets', 'services' => $query->where('technician_id', auth()->id()),
            'activities', 'technical_reports' => $query->whereHas('service', fn ($q) => $q->where('technician_id', auth()->id())),
            default => $query,
        };
    }
}
