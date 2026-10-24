<?php

namespace App\Modules\Workflow\UI\Pages;

use App\Modules\Workflow\UI\RecordActions;
use Filament\Actions\EditAction;

class ViewRecord extends \Filament\Resources\Pages\ViewRecord
{
    protected function getHeaderActions(): array
    {
        return [EditAction::make(), ...RecordActions::linked(static::getResource()::module())];
    }
}
