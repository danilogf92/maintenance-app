<?php

namespace App\Filament\Resources\Institutions\Pages;

use App\Filament\Resources\Institutions\InstitutionResource;
use App\Modules\Institution\InstitutionManager;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInstitution extends CreateRecord
{
    protected static string $resource = InstitutionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(InstitutionManager::class)->save(auth()->user(), $data);
    }
}
