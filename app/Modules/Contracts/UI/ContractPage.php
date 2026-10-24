<?php

namespace App\Modules\Contracts\UI;

use App\Livewire\Shared\RecordEditor;
use App\Modules\Contracts\Actions\SaveContract;
use App\Modules\Contracts\Configuration\Columns;
use App\Modules\Contracts\Configuration\Fields;
use App\Modules\Contracts\Configuration\Permissions;
use App\Modules\Contracts\Configuration\TableColumns;
use App\Modules\Contracts\Models\MaintenanceContract;
use App\Modules\Contracts\Queries\ContractQuery;
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class ContractPage extends RecordEditor
{
    public string $number = '';

    public string $name = '';

    public string $client_id = '';

    public string $notes = '';

    public bool $active = true;

    #[Locked]
    public int $lockVersion = 0;

    #[Locked]
    public string $code = '';

    #[Locked]
    public string $activeFilter = '';

    #[Locked]
    public array $loadedEditorRelations = [];

    public function boot(): void
    {
        Permissions::authorize();
    }

    protected function viewModule(): string
    {
        return 'contracts';
    }

    protected function modelClass(): string
    {
        return MaintenanceContract::class;
    }

    protected function editableFields(): array
    {
        return Fields::names();
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
        $this->activeFilter = $value;
    }

    public function mount(): void
    {
        parent::mount();
        if (request()->filled('record')) {
            $id = filter_var(request('record'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404);
            $this->edit($id);
        } elseif (request()->boolean('new')) {
            $this->create();
        }
    }

    public function create(): void
    {
        Permissions::authorize('manage');
        parent::create();
        $this->loadedEditorRelations = [];
    }

    public function edit(int $id): void
    {
        ContractQuery::scoped()->findOrFail($id);
        parent::edit($id);
        $this->loadedEditorRelations = [];
    }

    protected function fillRecord(Model $record): void
    {
        foreach (Fields::definitions() as $key => $definition) {
            $this->$key = $definition['type'] === 'checkbox' ? (bool) $record->$key : (string) $record->$key;
        }
        $this->lockVersion = $record->lock_version;
        $this->code = $record->code;
    }

    protected function resetRecord(): void
    {
        $this->reset(Fields::names());
        $this->lockVersion = 0;
        $this->code = '';
    }

    protected function persistRecord(): Model
    {
        return app(SaveContract::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }

    protected function storeCurrentRecord(): Model
    {
        $record = parent::storeCurrentRecord();
        $this->lockVersion = $record->lock_version;
        $this->code = $record->code;

        return $record;
    }

    protected function removeRecord(int $id): void
    {
        abort(403, 'Inactiva el contrato para conservar su historial.');
    }

    public function requestOpenRecordDelete(): void
    {
        abort(403);
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = ['name', 'number', 'client', 'status', 'equipment'];
        $this->saveViewSettings();
    }

    public function loadEditorRelation(string $relation): void
    {
        abort_unless($relation === 'contract_coverages', 422);
        Permissions::authorize();
        if ($this->formOpen && $this->finishCurrentRecord() && $this->editingId) {
            $this->loadedEditorRelations[$relation] = true;
        }
    }

    public function render()
    {
        $items = app(ContractQuery::class)->records($this->search, $this->activeFilter);
        $clientOptions = $this->formOpen && WorkflowPermissions::allows('clients') ? VisibleRecords::query('clients')->orderBy('name')->pluck('name', 'id')->all() : [];

        return view('contracts::page', array_merge(compact('items', 'clientOptions'), $this->operationViewsData(), $this->prepareTable($items), ContractEditor::configuration(), ['canManage' => Permissions::allows('manage')]))->layout('components.layouts.app');
    }
}
