<?php

namespace App\Modules\ReportTemplates\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplateVersion extends Model
{
    protected $fillable = ['report_template_id', 'number', 'definition', 'created_by'];

    protected function casts(): array
    {
        return ['definition' => 'array'];
    }
}
