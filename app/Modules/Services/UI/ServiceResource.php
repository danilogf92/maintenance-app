<?php

namespace App\Modules\Services\UI;

use App\Modules\Services\Models\Service;
use App\Modules\Workflow\UI\WorkflowResource;

class ServiceResource extends WorkflowResource
{
    protected static ?string $model = Service::class;
    protected static string $module = 'services';
    protected static ?string $slug = 'services';
    protected static ?int $navigationSort = 4;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
