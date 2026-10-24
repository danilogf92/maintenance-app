<?php
namespace App\Modules\TechnicalReports\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;

class ReportQuery
{
    public function records(string $search)
    {
        $query = VisibleRecords::query('technical_reports')
            ->select(['id', 'code', 'service_id', 'report_template_id', 'title', 'revision', 'created_at', 'updated_at'])->with('template:id,name');
        if (Permissions::allows('services')) { $query->with('service:id,code,technician_id'); }
        return $query->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$search.'%')->orWhere('title', 'like', '%'.$search.'%')))
            ->latest('id')->get();
    }

    public static function templates(): array
    {
        return \App\Modules\ReportTemplates\Models\ReportTemplate::orderBy('name')->get(['id', 'name', 'published_version_id'])
            ->mapWithKeys(fn ($t) => [$t->id => $t->name.($t->published_version_id ? ' · Publicada' : ' · Diseño en borrador')])->all();
    }
}
