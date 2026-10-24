<?php
use Illuminate\Support\Facades\Route;
Route::get('/operations/contacts', \App\Modules\Contacts\UI\ContactPage::class)->middleware('auth')->name('operations.contacts');
