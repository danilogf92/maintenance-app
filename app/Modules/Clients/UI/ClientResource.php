<?php

namespace App\Modules\Clients\UI;

use App\Modules\Clients\Models\Client;
use App\Modules\Workflow\UI\WorkflowResource;

class ClientResource extends WorkflowResource
{
    protected static ?string $model = Client::class;
    protected static string $module = 'clients';
    protected static ?string $slug = 'clients';
    protected static ?int $navigationSort = 1;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
