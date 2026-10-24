<?php

namespace App\Modules\TechnicalReports\UI;

use App\Modules\TechnicalReports\Models\TechnicalReport;
use App\Modules\Workflow\UI\WorkflowResource;

class TechnicalReportResource extends WorkflowResource
{
    protected static ?string $model = TechnicalReport::class;
    protected static string $module = 'technical_reports';
    protected static ?string $slug = 'technical-reports';
    protected static ?int $navigationSort = 6;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
