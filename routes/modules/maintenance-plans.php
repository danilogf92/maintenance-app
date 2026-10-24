<?php
use Illuminate\Support\Facades\Route;
Route::get('/operations/maintenance-plans', \App\Modules\MaintenancePlans\UI\PlanPage::class)->middleware('auth')->name('operations.maintenance_plans');

Route::get('/operations/maintenance-calendar', \App\Modules\MaintenancePlans\UI\CalendarPage::class)->middleware('auth')->name('operations.maintenance_calendar');
