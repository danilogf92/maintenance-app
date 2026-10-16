<?php

use App\Modules\Reports\UI\Controllers\TemplateTestPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/reports/preview/{generation}', TemplateTestPdfController::class)
    ->whereUuid('generation')->middleware(['auth', 'throttle:30,1'])->name('reports.preview');
