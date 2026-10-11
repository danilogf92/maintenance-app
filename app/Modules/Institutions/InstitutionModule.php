<?php

namespace App\Modules\Institutions;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;

/** Start here to understand and configure this module. */
class InstitutionModule
{
    public static function register(): void
    {
        View::addNamespace('institutions', __DIR__.'/UI/Views');
        Livewire::component('institutions-page', UI\InstitutionPage::class);
        Livewire::component('institution-related-users', UI\RelatedUsers::class);
        Livewire::component('institution-user-editor', UI\RelatedUserEditor::class);
    }

    public static function editor(?array $tabs = null): array
    {
        return app(UI\InstitutionEditor::class)->configuration($tabs);
    }
}
