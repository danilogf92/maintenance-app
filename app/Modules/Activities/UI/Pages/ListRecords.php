<?php

namespace App\Modules\Activities\UI\Pages;

use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Modules\Activities\UI\ActivityResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
