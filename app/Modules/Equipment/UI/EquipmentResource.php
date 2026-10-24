<?php

namespace App\Modules\Equipment\UI;

use App\Modules\Equipment\Models\Equipment;
use App\Modules\Workflow\UI\WorkflowResource;

class EquipmentResource extends WorkflowResource
{
    protected static ?string $model = Equipment::class;
    protected static string $module = 'equipment';
    protected static ?string $slug = 'equipment';
    protected static ?int $navigationSort = 2;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
