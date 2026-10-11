<?php

namespace App\Livewire\Shared;

use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Common editor lifecycle; modules define their own data and persistence rules. */
abstract class RecordEditor extends Component
{
    use ConfirmsRecordDeletion;
    use HasRecordViews;
    use SavesRecords;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public bool $formOpen = false;

    #[Locked]
    public bool $recordDirty = false;

    #[Locked]
    public string $editorSession = '';

    public string $search = '';

    abstract protected function modelClass(): string;

    abstract protected function editableFields(): array;

    abstract protected function fillRecord(Model $record): void;

    abstract protected function resetRecord(): void;

    abstract protected function persistRecord(): Model;

    abstract protected function removeRecord(int $id): void;

    public function boot(): void
    {
        Gate::authorize($this->viewModule().'.manage');
    }

    public function mount(): void
    {
        app(PageDataCache::class)->freshEntry($this->viewModule(), auth()->id());
        $this->initializeViews();
    }

    public function updated(string $property): void
    {
        if ($this->formOpen && in_array(explode('.', $property)[0], $this->editableFields(), true)) {
            $this->recordDirty = true;
        }
    }

    public function create(): void
    {
        if (! $this->finishCurrentRecord()) {
            return;
        }
        $this->editingId = null;
        $this->editorSession = (string) Str::uuid();
        $this->search = '';
        $this->resetRecord();
        $this->recordDirty = false;
        $this->formOpen = true;
        $this->cancelDelete();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        if (! $this->finishCurrentRecord()) {
            return;
        }
        $model = $this->modelClass();
        $record = $model::findOrFail($id);
        $this->fillRecord($record);
        $this->editingId = $id;
        $this->editorSession = (string) Str::uuid();
        $this->recordDirty = false;
        $this->formOpen = true;
        $this->cancelDelete();
        $this->resetValidation();
    }

    public function closeForm(): void
    {
        // A new incomplete draft must remain dismissible; valid drafts still save first.
        $newDraft = $this->formOpen && $this->editingId === null;
        if ($this->finishCurrentRecord() || $newDraft) {
            $this->cancel();
        }
    }

    public function cancel(): void
    {
        $this->formOpen = false;
        $this->editorSession = '';
        $this->recordDirty = false;
        $this->cancelDelete();
        $this->resetValidation();
    }

    protected function finishCurrentRecord(): bool
    {
        if (! $this->formOpen || ! $this->recordDirty) {
            return true;
        }
        try {
            $this->storeCurrentRecord();
            $this->recordDirty = false;

            return true;
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return false;
        }
    }

    protected function recordData(): array
    {
        $data = [];
        foreach ($this->editableFields() as $field) {
            $data[$field] = $this->$field;
        }

        return $data;
    }
}
