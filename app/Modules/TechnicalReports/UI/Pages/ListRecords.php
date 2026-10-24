<?php

namespace App\Modules\TechnicalReports\UI\Pages;

use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Modules\TechnicalReports\UI\TechnicalReportResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
