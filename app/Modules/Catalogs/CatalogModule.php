<?php
namespace App\Modules\Catalogs;

class CatalogModule
{
    public static function register(): void
    {
        view()->addNamespace('catalogs', __DIR__.'/UI/Views');
        \Livewire\Livewire::component('catalogs-page', UI\CatalogPage::class);
        foreach (Configuration\Catalogs::all() as $definition) { $definition['model']::observe(Queries\CatalogObserver::class); }
    }
}
