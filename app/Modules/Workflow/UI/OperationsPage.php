<?php

namespace App\Modules\Workflow\UI;

use App\Modules\Workflow\Configuration\{Modules, Permissions, EditorFields};
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Workflow\Actions\SaveRecord;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class OperationsPage extends Component
{
    use WithPagination, WithFileUploads;

    #[Locked] public string $module;
    #[Locked] public ?int $editingId = null;
    public array $form = [];
    public array $uploads = [];
    public string $search = '';
    public bool $formOpen = false;
    public string $notice = '';

    public function mount(string $module): void
    {
        $this->module = $module; Permissions::authorize($module);
        if (request()->filled('record')) {
            $id = filter_var(request('record'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); $this->edit($id);
        }
    }
    public function boot(): void { if (isset($this->module)) { Permissions::authorize($this->module); } }
    public function updatedSearch(): void { $this->resetPage(); }

    public function create(): void
    {
        Permissions::authorize($this->module, 'manage');
        $this->editingId = null; $this->form = EditorFields::defaults($this->module);
        $this->uploads = []; $this->formOpen = true; $this->notice = ''; $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $record = VisibleRecords::query($this->module)->findOrFail($id);
        $this->editingId = $id; $this->form = [];
        foreach (array_keys(EditorFields::defaults($this->module)) as $field) {
            $value = $record->$field;
            $this->form[$field] = $value instanceof \DateTimeInterface ? $value->format($field === 'opened_at' ? 'Y-m-d\TH:i' : 'Y-m-d') : $value;
        }
        $this->uploads = []; $this->formOpen = true; $this->notice = ''; $this->resetValidation();
    }

    public function save(): void
    {
        Permissions::authorize($this->module, 'manage');
        $this->validate(['uploads' => 'array|max:4', 'uploads.*' => 'image|mimes:png,jpg,jpeg,webp|max:1024']);
        if ($this->module === 'activities') {
            $this->validate(['form.photos' => 'array|max:4']);
            if (count($this->form['photos']) + count($this->uploads) > 4) { $this->addError('uploads', 'Máximo cuatro imágenes.'); return; }
            foreach ($this->uploads as $upload) { $this->form['photos'][] = $upload->store('service-photos/'.auth()->id(), 'local'); }
            $this->uploads = [];
        }
        try { $record = app(SaveRecord::class)->execute($this->module, $this->form, $this->editingId); }
        catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) { $this->addError('form.'.$field, $messages[0]); }
            return;
        }
        $this->edit($record->id); $this->notice = 'Registro guardado.';
    }

    public function generatePdf(int $id): void
    {
        abort_unless($this->module === 'technical_reports', 404);
        app(\App\Modules\TechnicalReports\Actions\QueueTechnicalPdf::class)->execute($id);
        $this->notice = 'PDF enviado a la cola. El enlace aparecerá cuando termine.';
    }

    public function render()
    {
        $query = VisibleRecords::query($this->module);
        $column = match ($this->module) { 'clients', 'equipment' => 'name', 'tickets' => 'subject', 'technical_reports' => 'title', 'activities' => 'description', default => 'service_type' };
        if ($this->search !== '') { $query->where(fn ($q) => $q->where('code', 'like', '%'.$this->search.'%')->orWhere($column, 'like', '%'.$this->search.'%')); }
        $columns = \App\Modules\Workflow\Configuration\TableColumns::make($this->module);
        $names = collect($columns)->reject(fn ($c) => in_array($c->getName(), ['updated_at', 'lastGeneration.status']))->mapWithKeys(fn ($c) => [$c->getName() => $c->getLabel()])->all();
        if ($this->module === 'technical_reports') { $query->with('lastGeneration'); }
        return view('workflow.operations', ['records' => $query->latest('id')->paginate(15), 'columns' => $names,
            'label' => Modules::get($this->module)['label'], 'fields' => EditorFields::make($this->module),
            'canManage' => Permissions::allows($this->module, 'manage')])->layout('components.layouts.app');
    }
}
