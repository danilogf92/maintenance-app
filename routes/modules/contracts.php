<?php
use Illuminate\Support\Facades\Route;
Route::get('/operations/contracts',\App\Modules\Contracts\UI\ContractPage::class)->middleware('auth')->name('operations.contracts');
