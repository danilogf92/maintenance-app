<?php

namespace App\Modules\TechnicalReports\Actions;

use App\Modules\TechnicalReports\Models\TechnicalReport;
use App\Modules\TechnicalReports\Queries\ReportData;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CaptureReport
{
    public function execute(TechnicalReport $report): void
    {
        $template = ReportTemplate::findOrFail($report->report_template_id);
        $published = $template->published_version_id ? $template->versions()->findOrFail($template->published_version_id) : null;
        $definition = Rules::validate($published?->definition ?? $template->definition);
        $revision = $report->snapshot ? $report->revision + 1 : 1;
        $definition['page']['footer'] = Str::limit($definition['page']['footer'], 55, '').' · BORRADOR '.$report->code.' R'.$revision;
        $data = app(ReportData::class)->execute($report->service, $report->code, $report->title);
        $snapshot = ['definition' => $definition, 'data' => $data,
            'branding' => app(TemplateRenderer::class)->captureBranding($definition, $data),
            'metadata' => ['report_id' => $report->id, 'revision' => $revision, 'template_version' => $published?->number,
                'captured_at' => now()->toIso8601String(), 'captured_by' => auth()->id()]];
        if (strlen(json_encode($snapshot)) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['report_template_id' => 'El informe supera 10 MiB. Reduce imágenes y logos.']);
        }
        $report->update(['snapshot' => $snapshot, 'revision' => $revision]);
    }
}
