<?php

namespace App\Modules\TechnicalReports\Actions;

use App\Modules\Reports\Jobs\GenerateReportPdf;
use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class QueueTechnicalPdf
{
    public function execute(int $id): ReportGeneration
    {
        $report = VisibleRecords::query('technical_reports')->findOrFail($id);
        $key = 'technical-pdf:'.auth()->id();
        abort_if(RateLimiter::tooManyAttempts($key, 10), 429, 'Espera un minuto antes de solicitar más PDF.');
        abort_if(ReportGeneration::where('user_id', auth()->id())->whereIn('status', ['queued', 'processing'])
            ->where('expires_at', '>', now())->count() >= 3, 429, 'Ya tienes tres PDF pendientes.');
        RateLimiter::hit($key, 60);

        return DB::transaction(function () use ($report) {
            $generation = ReportGeneration::create(['id' => (string) Str::uuid(), 'user_id' => auth()->id(),
                'report_template_id' => $report->report_template_id, 'technical_report_id' => $report->id,
                'report_revision' => $report->revision, 'snapshot' => $report->snapshot,
                'status' => 'queued', 'expires_at' => now()->addMinutes(config('report-pdf.ttl_minutes'))]);
            GenerateReportPdf::dispatch($generation->id)->onConnection('database')->onQueue('reports')->afterCommit();

            return $generation;
        });
    }
}
