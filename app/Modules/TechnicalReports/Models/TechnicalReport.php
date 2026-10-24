<?php

namespace App\Modules\TechnicalReports\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalReport extends Model
{
    protected $table = 'technical_reports';
    protected $guarded = ['id'];
    protected $hidden = ['snapshot'];

    protected function casts(): array
    {
        return ['snapshot' => 'encrypted:array'];
    }

    public function service()
    {
        return $this->belongsTo(\App\Modules\Services\Models\Service::class, 'service_id');
    }

    public function template()
    {
        return $this->belongsTo(\App\Modules\ReportTemplates\Models\ReportTemplate::class, 'report_template_id');
    }

    public function lastGeneration()
    {
        return $this->hasOne(\App\Modules\Reports\Models\ReportGeneration::class)->ofMany(['created_at' => 'max', 'id' => 'max'])->select(['report_generations.id', 'report_generations.technical_report_id', 'report_generations.status', 'report_generations.error', 'report_generations.expires_at', 'report_generations.report_revision', 'report_generations.created_at']);
    }

}
