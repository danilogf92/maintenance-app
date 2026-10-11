<?php

namespace App\Livewire\Shared;

use App\Modules\Shared\RecordCreation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

trait SavesRecords
{
    public function save(): void
    {
        $record = $this->storeCurrentRecord();
        $this->editingId = (int) $record->id;
        $this->cancel();
    }

    public function autosaveRecord(int $recordId, ?string $session = null): string
    {
        if (! $this->formOpen || ! $this->editingId || $recordId < 1 || (int) $this->editingId !== $recordId
            || ($session !== null && $session !== $this->editorSession)) {
            return 'stale';
        }
        try {
            $this->storeCurrentRecord();

            return 'saved';
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return 'invalid';
        }
    }

    public function autosaveDraft(string $session): string
    {
        if (! $this->formOpen || $session === '' || $session !== $this->editorSession) {
            return 'stale';
        }
        if ($this->editingId) {
            return 'saved';
        }
        try {
            $this->storeCurrentRecord();

            return 'saved';
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return 'invalid';
        }
    }

    protected function storeCurrentRecord(): Model
    {
        if (! $this->formOpen || (! $this->editingId && $this->editorSession === '')) {
            throw ValidationException::withMessages(['record' => 'Abre un registro antes de guardar.']);
        }
        $record = $this->editingId ? $this->persistRecord()
            : app(RecordCreation::class)->remember(auth()->id(), $this->viewModule(), $this->editorSession,
                $this->modelClass(), fn () => $this->persistRecord());
        $this->editingId = (int) $record->getKey();
        $this->recordDirty = false;
        if (property_exists($this, 'password')) {
            $this->password = '';
        }
        $this->resetValidation();
        $this->dispatch('operation-record-saved', recordId: $this->editingId);

        return $record;
    }
}
