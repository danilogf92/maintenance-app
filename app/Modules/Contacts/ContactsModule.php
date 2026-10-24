<?php
namespace App\Modules\Contacts;
use Livewire\Livewire;
class ContactsModule
{
    public static function register(): void
    {
        view()->addNamespace('contacts', __DIR__.'/UI/Views');
        Livewire::component('contacts-page', UI\ContactPage::class);
        Livewire::component('contact-links', UI\ContactLinks::class);
    }
}
