<?php

use App\Livewire\User\UserPage;
use Illuminate\Support\Facades\Route;

Route::get('/operations/users', UserPage::class)
    ->middleware(['auth', 'can:users.manage'])->name('operations.users');
