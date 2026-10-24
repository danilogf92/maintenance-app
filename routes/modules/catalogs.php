<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Catalogs\Configuration\Catalogs;
use App\Modules\Catalogs\UI\CatalogPage;

foreach (array_keys(Catalogs::all()) as $catalog) {
    Route::get('/operations/'.str_replace('_', '-', $catalog), CatalogPage::class)
        ->defaults('catalog', $catalog)->middleware('auth')->name('operations.'.$catalog);
}

Route::get('/operations/catalog-assets/{catalog}/{record}/{asset}', \App\Modules\Catalogs\UI\CatalogAssetController::class)
    ->whereNumber('record')->whereNumber('asset')->middleware(['auth', 'throttle:120,1'])->name('catalogs.asset');
