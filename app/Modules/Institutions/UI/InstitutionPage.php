<?php

namespace App\Modules\Institutions\UI;

use App\Livewire\Shared\RecordEditor;
use App\Models\Institution;
use App\Modules\Institutions\Actions\DeleteInstitution;
use App\Modules\Institutions\Actions\SaveInstitution;
use App\Modules\Institutions\Configuration\Columns;
use App\Modules\Institutions\Configuration\Fields;
use App\Modules\Institutions\Configuration\Relations;
use App\Modules\Institutions\Configuration\TableColumns;
use App\Modules\Institutions\InstitutionModule;
use App\Modules\Institutions\Queries\InstitutionQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

class InstitutionPage extends RecordEditor
{
    #[Locked]
    public array $loadedEditorRelations = [];

    public function create(): void
    {
        parent::create();
        if ($this->editingId === null) {
            $this->loadedEditorRelations = [];
        }
    }

    public function edit(int $id): void
    {
        $session = $this->editorSession;
        parent::edit($id);
        if ($this->editorSession !== $session) {
            $this->loadedEditorRelations = [];
        }
    }

    public function loadEditorRelation(string $relation): void
    {
        $definition = Relations::definitions()[$relation] ?? null;
        abort_unless($definition, 422);
        Gate::authorize($definition['permission']);
        if (! $this->formOpen || ! $this->finishCurrentRecord() || ! $this->editingId) {
            return;
        }
        if (! in_array($relation, $this->loadedEditorRelations, true)) {
            $this->loadedEditorRelations[] = $relation;
        }
    }

    public string $typeFilter = '';

    public string $name = '';

    public string $code = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $city = '';

    public string $type = 'company';

    protected function modelClass(): string
    {
        return Institution::class;
    }

    protected function editableFields(): array
    {
        return Fields::names();
    }

    protected function viewModule(): string
    {
        return 'institutions';
    }

    protected function viewFields(): array
    {
        return Columns::columns();
    }

    protected function tableColumns(): array
    {
        return TableColumns::definitions();
    }

    protected function defaultViews(): array
    {
        return Columns::views();
    }

    protected function applyViewFilter(string $value): void
    {
        $this->typeFilter = $value;
    }

    protected function resetRecord(): void
    {
        $this->reset(Fields::names());
    }

    protected function fillRecord(Model $record): void
    {
        foreach ($this->editableFields() as $field) {
            $this->$field = (string) ($record->$field ?? '');
        }
    }

    protected function persistRecord(): Model
    {
        $target = $this->editingId ? Institution::findOrFail($this->editingId) : null;

        return app(SaveInstitution::class)->execute(auth()->user(), $this->recordData(), $target);
    }

    protected function removeRecord(int $id): void
    {
        app(DeleteInstitution::class)->execute(auth()->user(), $id);
    }

    public function render()
    {
        $items = app(InstitutionQuery::class)->records(auth()->id(), $this->search, $this->typeFilter);

        return view('institutions::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), InstitutionModule::editor()))
            ->layout('components.layouts.app');
    }
}
