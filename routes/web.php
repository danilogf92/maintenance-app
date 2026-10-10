<?php

use App\Http\Controllers\SessionController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(Auth::check() ? '/dashboard' : '/login'));
Route::get('/login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:6,1']);
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/dashboard', Home::class)->middleware('auth')->name('dashboard');
