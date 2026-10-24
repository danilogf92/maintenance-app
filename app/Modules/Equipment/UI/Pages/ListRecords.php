<?php

namespace App\Modules\Equipment\UI\Pages;

use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Modules\Equipment\UI\EquipmentResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
