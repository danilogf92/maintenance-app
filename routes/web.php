<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(Auth::check() ? '/dashboard' : '/login'));
Route::get('/login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:6,1']);
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/dashboard', Home::class)->middleware('auth')->name('dashboard');

Route::get('/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

require __DIR__.'/modules/user.php';
require __DIR__.'/modules/institution.php';
require __DIR__.'/modules/report.php';
