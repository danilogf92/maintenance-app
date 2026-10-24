<?php
namespace App\Modules\FieldEngineers;
class FieldEngineerModule
{
    public static function register(): void
    {
        view()->addNamespace('field-engineers',__DIR__.'/UI/Views');
        \Livewire\Livewire::component('field-engineers-page',UI\EngineerPage::class);
    }
}
