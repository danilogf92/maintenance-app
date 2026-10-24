<?php

namespace App\Modules\Tickets\UI\Pages;

use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Modules\Tickets\UI\TicketResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
