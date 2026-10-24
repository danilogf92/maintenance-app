<?php
use App\Modules\MaintenanceProtocols\UI\{ProtocolPage, Controllers\ProtocolFileController};
use Illuminate\Support\Facades\Route;
Route::middleware('auth')->group(function () {
    Route::get('/operations/maintenance-protocols', ProtocolPage::class)->name('operations.maintenance_protocols');
    Route::get('/operations/protocol-files/{file}', ProtocolFileController::class)->name('operations.protocol-file');
});
