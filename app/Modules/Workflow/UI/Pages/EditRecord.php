<?php

namespace App\Modules\Workflow\UI\Pages;

use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Database\Eloquent\Model;

class EditRecord extends \Filament\Resources\Pages\EditRecord
{
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try { $saved = app(SaveRecord::class)->execute(static::getResource()::module(), $data, $record->id); }
        catch (\Illuminate\Validation\ValidationException $error) {
            \Filament\Notifications\Notification::make()->danger()->title('No se pudo guardar')
                ->body(implode(' ', \Illuminate\Support\Arr::flatten($error->errors())))->send();
            throw \Illuminate\Validation\ValidationException::withMessages(collect($error->errors())->mapWithKeys(fn ($messages, $key) => ['data.'.$key => $messages])->all());
        }
        $record->setRawAttributes($saved->getAttributes(), true);

        return $record;
    }

    protected function afterSave(): void
    {
        $this->data['lock_version'] = $this->record->lock_version;
    }
}
