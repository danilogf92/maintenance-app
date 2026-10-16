<?php

namespace App\Modules\Reports\Actions;

use App\Modules\Reports\Jobs\GenerateReportPdf;
use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\ReportTemplates\Configuration\Permissions;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Queries\TemplateSampleData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class QueueReportPdf
{
    public function execute(int $templateId, array $definition, int $count, ?int $institutionId): ReportGeneration
    {
        Permissions::authorize();
        $definition = Rules::validate($definition);
        abort_unless(in_array($count, [4, 24, 80]), 422);
        $key = 'report-pdf:'.auth()->id();
        abort_if(RateLimiter::tooManyAttempts($key, 10), 429, 'Espera un minuto antes de generar más pruebas.');
        abort_if(ReportGeneration::where('user_id', auth()->id())->whereIn('status', ['queued', 'processing'])
            ->where('expires_at', '>', now())->count() >= 3, 429, 'Ya tienes tres pruebas pendientes.');
        RateLimiter::hit($key, 60);
        $data = app(TemplateSampleData::class)->execute($count, $institutionId);
        $snapshot = ['definition' => $definition, 'data' => $data,
            'branding' => app(TemplateRenderer::class)->captureBranding($definition, $data)];
        if (strlen(json_encode($snapshot)) > 8 * 1024 * 1024) {
            throw ValidationException::withMessages(['pdf' => 'La prueba tiene demasiadas imágenes de logos. Reduce su tamaño o los encabezados duplicados.']);
        }
        return DB::transaction(function () use ($templateId, $snapshot) {
            $generation = ReportGeneration::create(['id' => (string) Str::uuid(), 'user_id' => auth()->id(),
                'report_template_id' => $templateId, 'status' => 'queued',
                'snapshot' => $snapshot,
                'expires_at' => now()->addMinutes(config('report-pdf.ttl_minutes'))]);
            GenerateReportPdf::dispatch($generation->id)->onConnection('database')->onQueue('reports')->afterCommit();

            return $generation;
        });
    }
}
