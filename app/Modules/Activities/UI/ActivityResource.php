<?php

namespace App\Modules\Activities\UI;

use App\Modules\Activities\Models\Activity;
use App\Modules\Workflow\UI\WorkflowResource;

class ActivityResource extends WorkflowResource
{
    protected static ?string $model = Activity::class;
    protected static string $module = 'activities';
    protected static ?string $slug = 'activities';
    protected static ?int $navigationSort = 5;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
