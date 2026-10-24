<?php

namespace App\Modules\Workflow\UI;

use App\Livewire\Shared\RecordEditor;
use App\Modules\Workflow\Configuration\{Modules, Permissions};
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

abstract class TabbedRecordPage extends RecordEditor
{
    #[Locked] public int $lockVersion = 0;
    #[Locked] public string $code = '';
    #[Locked] public bool $relationLoaded = false;
    #[Locked] public array $loadedEditorRelations = [];

    abstract protected function relatedModule(): string;
    protected function relatedModules(): array { return [$this->relatedModule()]; }

    public function boot(): void { Permissions::authorize($this->viewModule()); }

    public function mount(): void
    {
        parent::mount();
        if (request()->filled('record')) {
            $id = filter_var(request('record'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); $this->edit($id);
        }
    }

    protected function modelClass(): string { return Modules::get($this->viewModule())['model']; }
    protected function editableFields(): array { return Modules::configuration($this->viewModule(), 'Fields')::names(); }

    public function create(): void
    {
        Permissions::authorize($this->viewModule(), 'manage');
        $session = $this->editorSession; parent::create();
        if ($session !== $this->editorSession) { $this->relationLoaded = false; $this->loadedEditorRelations = []; }
    }

    public function edit(int $id): void
    {
        VisibleRecords::query($this->viewModule())->findOrFail($id);
        $session = $this->editorSession; parent::edit($id);
        if ($session !== $this->editorSession) { $this->relationLoaded = false; $this->loadedEditorRelations = []; }
    }

    protected function fillRecord(Model $record): void
    {
        $fields = Modules::configuration($this->viewModule(), 'Fields')::definitions();
        foreach ($fields as $field => $definition) {
            $value = $record->$field;
            $this->$field = match ($definition['type']) {
                'checkbox' => (bool) $value,
                'evidence' => (array) ($value ?? []),
                'date' => $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : (string) ($value ?? ''),
                'datetime-local' => $value instanceof \DateTimeInterface ? $value->format('Y-m-d\\TH:i') : (string) ($value ?? ''),
                default => (string) ($value ?? ''),
            };
        }
        $this->lockVersion = $record->lock_version; $this->code = $record->code;
    }

    protected function resetRecord(): void
    {
        $this->reset($this->editableFields()); $this->lockVersion = 0; $this->code = '';
    }

    protected function storeCurrentRecord(): Model
    {
        $record = parent::storeCurrentRecord();
        $this->lockVersion = $record->lock_version; $this->code = $record->code;
        return $record;
    }

    protected function removeRecord(int $id): void { abort(403, 'Actualiza el estado del registro para conservar su historial.'); }
    public function requestOpenRecordDelete(): void { abort(403, 'Actualiza el estado del registro para conservar su historial.'); }

    public function loadEditorRelation(string $relation): void
    {
        abort_unless(in_array($relation, $this->relatedModules(), true), 422);
        Permissions::authorize($relation);
        if (! $this->formOpen || ! $this->finishCurrentRecord() || ! $this->editingId) { return; }
        VisibleRecords::query($this->viewModule())->findOrFail($this->editingId);
        $this->relationLoaded = true; $this->loadedEditorRelations[$relation] = true;
    }
}
