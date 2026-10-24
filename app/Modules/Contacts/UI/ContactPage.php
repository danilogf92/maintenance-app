<?php
namespace App\Modules\Contacts\UI;
use App\Livewire\Shared\RecordEditor;
use App\Modules\Contacts\Configuration\{Fields, Columns, TableColumns, Permissions};
use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Queries\{ContactQuery, ContactParents};
use App\Modules\Contacts\Actions\SaveContact;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
class ContactPage extends RecordEditor
{
    public string $name = '', $position = '', $department = '', $email = '', $phone = '', $extension = '', $notes = '';
    public bool $active = true;
    #[Locked] public int $lockVersion = 0;
    #[Locked] public string $code = '', $activeFilter = '';
    #[Locked] public array $loadedEditorRelations = [], $pendingParent = [];
    public function boot(): void { Permissions::authorize(); }
    protected function viewModule(): string { return 'contacts'; }
    protected function modelClass(): string { return Contact::class; }
    protected function editableFields(): array { return Fields::names(); }
    protected function viewFields(): array { return Columns::columns(); }
    protected function tableColumns(): array { return TableColumns::definitions(); }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->activeFilter = $value; }
    public function mount(): void
    {
        parent::mount();
        $keys = array_filter(['record', 'new_for_client', 'new_for_institution'], fn ($key) => request()->filled($key));
        abort_if(count($keys) > 1, 422);
        foreach ($keys as $key) {
            $id = filter_var(request($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404);
            if ($key === 'record') { $this->edit($id); return; }
            $type = str_replace('new_for_', '', $key); ContactParents::find($type, $id);
            $this->create(); $this->pendingParent = ['type' => $type, 'id' => $id];
        }
    }
    public function create(): void
    {
        Permissions::authorize('manage'); $session = $this->editorSession; parent::create();
        if ($session !== $this->editorSession) { $this->loadedEditorRelations = []; $this->pendingParent = []; }
    }
    public function edit(int $id): void
    {
        $session = $this->editorSession; parent::edit($id);
        if ($session !== $this->editorSession) { $this->loadedEditorRelations = []; $this->pendingParent = []; }
    }
    protected function fillRecord(Model $record): void
    {
        foreach (Fields::names() as $field) { $this->$field = $field === 'active' ? (bool) $record->$field : (string) $record->$field; }
        $this->lockVersion = $record->lock_version; $this->code = $record->code;
    }
    protected function resetRecord(): void { $this->reset(Fields::names()); $this->lockVersion = 0; $this->code = ''; }
    protected function persistRecord(): Model
    {
        return app(SaveContact::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId, $this->pendingParent);
    }
    protected function storeCurrentRecord(): Model
    {
        $record = parent::storeCurrentRecord(); $this->lockVersion = $record->lock_version;
        $this->code = $record->code; $this->pendingParent = []; return $record;
    }
    protected function removeRecord(int $id): void { abort(403, 'Inactiva el contacto para conservar su historial.'); }
    public function requestOpenRecordDelete(): void { abort(403); }
    public function resetColumns(): void { $this->visibleColumns = ['name', 'code', 'position', 'email', 'phone', 'status']; $this->saveViewSettings(); }
    public function loadEditorRelation(string $relation): void
    {
        abort_unless($relation === 'contacts', 422); Permissions::authorize();
        if ($this->formOpen && $this->finishCurrentRecord() && $this->editingId) { $this->loadedEditorRelations[$relation] = true; }
    }
    public function render()
    {
        $items = app(ContactQuery::class)->records($this->search, $this->activeFilter);
        return view('contacts::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), ContactEditor::configuration(),
            ['canManage' => Permissions::allows('manage'), 'canViewRelation' => true]))->layout('components.layouts.app');
    }
}
