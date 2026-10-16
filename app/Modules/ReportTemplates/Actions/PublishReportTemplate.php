<?php

namespace App\Modules\ReportTemplates\Actions;

use App\Modules\ReportTemplates\Configuration\Permissions;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Illuminate\Support\Facades\DB;

class PublishReportTemplate
{
    public function execute(int $id): void
    {
        Permissions::authorize();
        DB::transaction(function () use ($id) {
            $template = ReportTemplate::lockForUpdate()->findOrFail($id);
            $version = $template->versions()->create(['number' => $template->versions()->max('number') + 1,
                'definition' => $template->definition, 'created_by' => auth()->id()]);
            $template->update(['published_version_id' => $version->id]);
        });
    }
}
