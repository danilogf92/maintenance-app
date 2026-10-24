<?php

use App\Modules\Access\UI\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google', [GoogleController::class, 'redirect'])->middleware(['guest', 'throttle:10,1'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
Route::post('/profile/google/connect', [GoogleController::class, 'connect'])->middleware(['auth', 'throttle:10,1'])->name('google.connect');
Route::delete('/profile/google', [GoogleController::class, 'disconnect'])->middleware('auth')->name('google.disconnect');
