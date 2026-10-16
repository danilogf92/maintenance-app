<?php

namespace App\Modules\ReportTemplates;

use Illuminate\Support\Facades\View;

class ReportTemplateModule
{
    public static function register(): void
    {
        View::addNamespace('report-templates', __DIR__.'/UI/Views');
        View::addNamespace('report-print', __DIR__.'/Rendering/Views');
    }
}
