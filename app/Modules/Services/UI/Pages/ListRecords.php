<?php

namespace App\Modules\Services\UI\Pages;

use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Modules\Services\UI\ServiceResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
