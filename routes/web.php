<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.welcome')->name('welcome');
Route::get('/login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:6,1']);
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/dashboard', Home::class)->middleware('auth')->name('dashboard');

Route::get('/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

require __DIR__.'/modules/user.php';
require __DIR__.'/modules/institution.php';
require __DIR__.'/modules/report.php';

require __DIR__.'/modules/workflow.php';
require __DIR__.'/modules/catalogs.php';
require __DIR__.'/modules/contacts.php';
require __DIR__.'/modules/maintenance-plans.php';

require __DIR__.'/modules/access.php';

Route::get('/operations/data-review', \App\Modules\DataReview\UI\ReviewPage::class)->middleware('auth')->name('operations.data-review');

Route::get('/operations/field-engineers', \App\Modules\FieldEngineers\UI\EngineerPage::class)->middleware('auth')->name('operations.field_engineers');

require __DIR__.'/modules/contracts.php';

require __DIR__.'/modules/maintenance-protocols.php';
