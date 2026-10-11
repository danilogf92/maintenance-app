<?php

use App\Modules\Institutions\UI\InstitutionPage;
use Illuminate\Support\Facades\Route;

Route::get('/operations/institutions', InstitutionPage::class)
    ->middleware(['auth', 'can:institutions.manage'])->name('operations.institutions');
