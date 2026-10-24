<?php

namespace App\Modules\Contracts;

use Livewire\Livewire;

class ContractsModule
{
    public static function register(): void
    {
        view()->addNamespace('contracts', __DIR__.'/UI/Views');
        Livewire::component('contracts-page', UI\ContractPage::class);
        Livewire::component('contract-coverages', UI\Coverages::class);
    }
}
