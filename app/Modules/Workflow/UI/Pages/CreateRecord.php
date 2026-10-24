<?php

namespace App\Modules\Workflow\UI\Pages;

use App\Modules\Workflow\Actions\SaveRecord;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Database\Eloquent\Model;

class CreateRecord extends \Filament\Resources\Pages\CreateRecord
{
    protected function afterFill(): void
    {
        $module = static::getResource()::module();
        $parent = ['equipment' => ['client_id', 'clients'], 'tickets' => ['equipment_id', 'equipment'],
            'services' => ['ticket_id', 'tickets'], 'activities' => ['service_id', 'services'], 'technical_reports' => ['service_id', 'services']][$module] ?? null;
        if ($parent && ($id = request()->integer($parent[0]))) {
            VisibleRecords::query($parent[1])->findOrFail($id); $this->data[$parent[0]] = $id;
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        try { return app(SaveRecord::class)->execute(static::getResource()::module(), $data); }
        catch (\Illuminate\Validation\ValidationException $error) {
            \Filament\Notifications\Notification::make()->danger()->title('No se pudo guardar')
                ->body(implode(' ', \Illuminate\Support\Arr::flatten($error->errors())))->send();
            throw \Illuminate\Validation\ValidationException::withMessages(collect($error->errors())->mapWithKeys(fn ($messages, $key) => ['data.'.$key => $messages])->all());
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record->id]);
    }
}
