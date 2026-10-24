<?php

namespace App\Modules\Clients\UI\Pages;

use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Modules\Clients\UI\ClientResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
