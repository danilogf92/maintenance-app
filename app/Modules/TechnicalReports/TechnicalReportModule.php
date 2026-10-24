<?php
namespace App\Modules\TechnicalReports;

class TechnicalReportModule
{
    public static function register(): void
    {
        view()->addNamespace('technical-reports', __DIR__.'/UI/Views');
        \Livewire\Livewire::component('technical-reports-page', UI\TechnicalReportPage::class);
    }
}
