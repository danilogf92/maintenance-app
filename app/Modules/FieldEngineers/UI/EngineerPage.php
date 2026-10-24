<?php
namespace App\Modules\FieldEngineers\UI;
use App\Livewire\Shared\RecordEditor;
use App\Modules\FieldEngineers\Configuration\{Fields,Tabs,Layout,Columns,TableColumns,Permissions};
use App\Modules\FieldEngineers\Models\FieldEngineer;
use App\Modules\FieldEngineers\Queries\EngineerQuery;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
class EngineerPage extends RecordEditor
{
    #[Locked] public string $name='', $initials='', $country='', $city='', $email='', $phone='', $code='';
    public function boot(): void { Permissions::authorize(); }
    protected function viewModule(): string { return 'field_engineers'; }
    protected function modelClass(): string { return FieldEngineer::class; }
    protected function editableFields(): array { return []; }
    protected function viewFields(): array { return Columns::columns(); }
    protected function tableColumns(): array { return TableColumns::definitions(); }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void {}
    public function mount(): void
    {
        parent::mount();if (request()->filled('record')) {
            $id=filter_var(request('record'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);abort_unless($id,404);$this->edit($id);
        }
    }
    public function edit(int $id): void { Permissions::authorize();parent::edit($id); }
    public function create(): void { abort(403); }
    protected function fillRecord(Model $record): void { foreach ([...Fields::names(),'code'] as $field) { $this->$field=(string)($record->$field ?? ''); } }
    protected function resetRecord(): void { $this->reset([...Fields::names(),'code']); }
    protected function persistRecord(): Model { abort(403); }
    protected function removeRecord(int $id): void { abort(403); }
    public function requestOpenRecordDelete(): void { abort(403); }
    public function resetColumns(): void { $this->visibleColumns=['name','initials','city','email','phone'];$this->saveViewSettings(); }
    public function render()
    {
        $query=app(EngineerQuery::class);$items=$query->records($this->search);
        return view('field-engineers::page',array_merge(compact('items'),$this->operationViewsData(),$this->prepareTable($items),[
            'editorTabs'=>Tabs::definitions(),'editorFields'=>Fields::definitions(),'editorLayoutStyle'=>Layout::styles(),
            'canManage'=>false,'canViewRelation'=>false,'engineerDetails'=>$query->details($this->formOpen ? $this->editingId : null)
        ]))->layout('components.layouts.app');
    }
}
