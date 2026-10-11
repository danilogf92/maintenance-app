<?php

namespace App\Livewire\Shared;

use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait ConfirmsRecordDeletion
{
    #[Locked]
    public bool $confirmDelete = false;

    #[Locked]
    public ?int $recordDeletionId = null;

    #[Locked]
    public string $recordDeletionName = '';

    public function requestOpenRecordDelete(): void
    {
        if (! $this->formOpen || ! $this->editingId) {
            return;
        }
        $model = $this->modelClass();
        $record = $model::findOrFail($this->editingId);
        $this->recordDeletionId = (int) $record->id;
        $this->recordDeletionName = (string) $record->name;
        $this->confirmDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmDelete = false;
        $this->recordDeletionId = null;
        $this->recordDeletionName = '';
    }

    public function deleteConfirmedRecord(): void
    {
        if (! $this->confirmDelete || ! $this->recordDeletionId) {
            return;
        }
        $id = $this->recordDeletionId;
        try {
            $this->removeRecord($id);
            $this->cancelDelete();
            if ((int) $this->editingId === $id) {
                $this->formOpen = false;
                $this->editingId = null;
                $this->recordDirty = false;
                $this->resetValidation();
            }
        } catch (ValidationException $exception) {
            $this->cancelDelete();
            session()->flash('error', $exception->validator->errors()->first());
        }
    }
}
