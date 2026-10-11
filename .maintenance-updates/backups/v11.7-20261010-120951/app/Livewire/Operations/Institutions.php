<?php
namespace App\Livewire\Operations;

use App\Livewire\Concerns\HasOperationViews;
use App\Models\Institution;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Institutions extends Component
{
    use HasOperationViews;

    public string $search = '';
    public string $typeFilter = '';
    public ?int $editingId = null;
    public bool $formOpen = false;
    public string $name = '';
    public string $code = '';
    public string $type = 'company';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $city = '';

    public array $selectedIds = [];
    public bool $confirmDelete = false;

    public function requestDelete(): void
    {
        Gate::authorize('institutions.manage');
        $this->selectedIds = array_values(array_unique(array_filter(array_map('intval', $this->selectedIds), fn ($id) => $id > 0)));
        $this->confirmDelete = count($this->selectedIds) > 0;
    }

    public function cancelDelete(): void
    {
        $this->confirmDelete = false;
        $this->selectedIds = [];
    }

    public function deleteSelected(): void
    {
        Gate::authorize('institutions.manage');
        if (!$this->confirmDelete) return;
        $ids = array_values(array_unique(array_filter(array_map('intval', $this->selectedIds), fn ($id) => $id > 0)));
        if (!$ids || count($ids) > 100) { $this->cancelDelete(); return; }

        if (Institution::whereIn('id', $ids)->whereHas('users')->exists()) {
            session()->flash('error', 'No se pueden eliminar instituciones que tengan usuarios asociados.');
            $this->cancelDelete(); return;
        }

        Institution::whereIn('id', $ids)->delete();
        $this->cancelDelete();
        session()->flash('success', 'Registros eliminados correctamente.');
    }

    public function mount(): void { Gate::authorize('institutions.manage'); $this->initializeViews(); }
    public function updatedSearch(): void { }
    public function updatedTypeFilter(): void { }
    public function create(): void
    {
        Gate::authorize('institutions.manage');
        $this->reset(['editingId', 'name', 'code', 'contact_email', 'contact_phone', 'city']);
        $this->type = 'company'; $this->resetValidation(); $this->formOpen = true;
    }
    public function edit(int $id): void
    {
        Gate::authorize('institutions.manage');
        $item = Institution::findOrFail($id);
        $this->editingId = $item->id;
        foreach (['name','code','type','contact_email','contact_phone','city'] as $field) {
            $this->$field = (string) ($item->$field ?? '');
        }
        $this->resetValidation(); $this->formOpen = true;
    }
    public function cancel(): void { $this->formOpen = false; $this->resetValidation(); }
    /** Close the drawer: persist complete forms; discard incomplete drafts. */
    public function closeForm(): void
    {
        if (! $this->formOpen) return;
        if (trim($this->name) === '' || trim($this->code) === '') {
            $this->cancel();
            return;
        }
        $this->save();
    }

    public function save(): void
    {
        Gate::authorize('institutions.manage');
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => ['required','string','max:255', Rule::unique('institutions','code')->ignore($this->editingId)],
            'type' => 'required|in:hospital,clinic,university,company,other',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:80',
            'city' => 'nullable|string|max:120',
        ]);
        $item = $this->editingId ? Institution::findOrFail($this->editingId) : new Institution;
        $item->fill($data)->save();
        $this->formOpen = false;
        session()->flash('success', 'Institución guardada correctamente.');
    }
    protected function viewModule(): string { return 'institutions'; }
    protected function viewFields(): array { return ['name' => 'Nombre', 'code' => 'Código', 'type' => 'Tipo', 'city' => 'Ciudad', 'users' => 'Usuarios']; }
    protected function defaultViews(): array { return [
        ['name' => 'Todos', 'filter' => ''],
        ['name' => 'Empresas', 'filter' => 'company'],
        ['name' => 'Hospitales', 'filter' => 'hospital'],
        ['name' => 'Clínicas', 'filter' => 'clinic'],
        ['name' => 'Universidades', 'filter' => 'university'],
        ['name' => 'Otros', 'filter' => 'other'],
    ]; }
    protected function applyViewFilter(string $value): void { $this->typeFilter = $value; }
    public function render()
    {
        Gate::authorize('institutions.manage');
        $items = Institution::query()->withCount('users')
            ->when($this->search, fn($q) => $q->where(fn($x) => $x->where('name','like','%'.$this->search.'%')->orWhere('code','like','%'.$this->search.'%')))
            ->when($this->typeFilter, fn($q) => $q->where('type',$this->typeFilter))
            ->orderBy('name')->get();
        return view('livewire.operations.institutions',array_merge(compact('items'), $this->operationViewsData()))->layout('components.layouts.app');
    }
}
