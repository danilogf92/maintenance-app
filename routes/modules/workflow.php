<?php

use App\Modules\Workflow\UI\Controllers\ActivityPhotoController;
use App\Modules\Workflow\UI\Controllers\TechnicalPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/workflow/photos/{activity}/{photo}', ActivityPhotoController::class)
    ->whereNumber('activity')->whereNumber('photo')->middleware(['auth', 'throttle:60,1'])->name('workflow.photo');
Route::get('/workflow/reports/{report}/pdf/{generation}', TechnicalPdfController::class)
    ->whereNumber('report')->whereUuid('generation')->middleware(['auth', 'throttle:30,1'])->name('workflow.pdf');

Route::get('/operations/services/{service}/documents/{document}', \App\Modules\Services\UI\Controllers\ServiceDocumentController::class)
    ->whereNumber('service')->whereNumber('document')->middleware(['auth', 'throttle:60,1'])->name('services.document');

foreach (array_keys(\App\Modules\Workflow\Configuration\Modules::all()) as $module) {
    Route::get('/operations/'.str_replace('_', '-', $module), match ($module) { 'clients' => \App\Modules\Clients\UI\ClientPage::class, 'equipment' => \App\Modules\Equipment\UI\EquipmentPage::class, 'tickets' => \App\Modules\Tickets\UI\TicketPage::class, 'services' => \App\Modules\Services\UI\ServicePage::class, 'activities' => \App\Modules\Activities\UI\ActivityPage::class, 'technical_reports' => \App\Modules\TechnicalReports\UI\TechnicalReportPage::class, default => \App\Modules\Workflow\UI\OperationsPage::class })
        ->defaults('module', $module)->middleware('auth')->name('operations.'.$module);
}
