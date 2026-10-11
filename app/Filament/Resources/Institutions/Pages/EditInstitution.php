<?php

namespace App\Filament\Resources\Institutions\Pages;

use App\Filament\Resources\Institutions\InstitutionResource;
use App\Modules\Institution\InstitutionManager;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditInstitution extends EditRecord
{
    protected static string $resource = InstitutionResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(InstitutionManager::class)->save(auth()->user(), $data, $record);
    }
}
