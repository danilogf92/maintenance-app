<?php

namespace App\Modules\ReportTemplates\Actions;

use App\Modules\ReportTemplates\Configuration\Permissions;
use App\Modules\ReportTemplates\Models\ReportTemplate;

class DuplicateReportTemplate
{
    public function execute(ReportTemplate $template): ReportTemplate
    {
        Permissions::authorize();

        return ReportTemplate::create(['name' => mb_substr($template->name, 0, 110).' (copia)',
            'description' => $template->description, 'definition' => $template->definition]);
    }
}
