<?php

namespace App\Modules\ReportTemplates\UI\Pages;

use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\UI\ReportTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReportTemplate extends CreateRecord
{
    protected static string $resource = ReportTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'definition' => Blocks::defaults()];
    }

    protected function getRedirectUrl(): string
    {
        return ReportTemplateResource::getUrl('edit', ['record' => $this->record->id]);
    }
}
