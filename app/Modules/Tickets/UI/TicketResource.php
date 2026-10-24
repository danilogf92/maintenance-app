<?php

namespace App\Modules\Tickets\UI;

use App\Modules\Tickets\Models\Ticket;
use App\Modules\Workflow\UI\WorkflowResource;

class TicketResource extends WorkflowResource
{
    protected static ?string $model = Ticket::class;
    protected static string $module = 'tickets';
    protected static ?string $slug = 'tickets';
    protected static ?int $navigationSort = 3;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
