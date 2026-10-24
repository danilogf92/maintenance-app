<?php

namespace App\Modules\TechnicalReports\Configuration;

use Illuminate\Validation\Rule;

class Rules
{
    public static function make(): array
    {
        return ['service_id' => 'required|integer|exists:services,id', 'report_template_id' => 'required|integer|exists:report_templates,id', 'title' => 'required|string|max:160'];
    }
}
