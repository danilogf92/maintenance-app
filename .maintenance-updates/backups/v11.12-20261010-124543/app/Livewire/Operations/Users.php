<?php
namespace App\Livewire\Operations;

use App\Livewire\Concerns\HasOperationViews;
use App\Models\Institution;
use App\Models\User;
use App\Services\UserManager;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    use HasOperationViews;

    public string $search = '';
    public string $institutionFilter = '';
    public string $activeFilter = '';
    public ?int $editingId = null;
    public bool $formOpen = false;
    public string $name = '', $email = '', $identification = '', $phone = '', $job_title = '', $password = '';
    public string $institution_id = '';
    public bool $is_active = true, $can_access_admin = false;
    public array $role_ids = [];

    public array $selectedIds = [];
    public bool $confirmDelete = false;

    public function requestDelete(): void
    {
        Gate::authorize('users.manage');
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
        Gate::authorize('users.manage');
        if (!$this->confirmDelete) return;
        $ids = array_values(array_unique(array_filter(array_map('intval', $this->selectedIds), fn ($id) => $id > 0)));
        if (!$ids || count($ids) > 100) { $this->cancelDelete(); return; }

        $records = User::whereIn('id', $ids)->get();
        foreach ($records as $record) {
            if ($record->id === auth()->id() || $record->hasRole('SuperAdmin') || $record->can_access_admin) {
                session()->flash('error', 'No se permite eliminar tu cuenta ni cuentas administrativas.');
                $this->cancelDelete(); return;
            }
        }

        User::whereIn('id', $ids)->delete();
        $this->cancelDelete();
        session()->flash('success', 'Registros eliminados correctamente.');
    }

    public function mount(): void { Gate::authorize('users.manage'); $this->initializeViews(); }
    public function updatedSearch(): void { }
    public function updatedInstitutionFilter(): void { }
    public function updatedActiveFilter(): void { }
    public function create(): void
    {
        Gate::authorize('users.manage');
        $this->reset(['editingId','name','email','identification','phone','job_title','password','institution_id','role_ids','can_access_admin']);
        $this->is_active = true; $this->resetValidation(); $this->formOpen = true;
    }
    public function edit(int $id): void
    {
        Gate::authorize('users.manage');
        $user = User::findOrFail($id);
        if (!auth()->user()->hasRole('SuperAdmin') && ($user->hasRole('SuperAdmin') || $user->can_access_admin)) abort(403);
        $this->editingId = $user->id;
        foreach (['name','email','identification','phone','job_title'] as $key) $this->$key = (string) ($user->$key ?? '');
        $this->institution_id = (string) $user->institution_id;
        $this->is_active = (bool) $user->is_active;
        $this->can_access_admin = (bool) $user->can_access_admin;
        $this->role_ids = $user->roles->pluck('id')->map(fn($id) => (string)$id)->all();
        $this->password = ''; $this->resetValidation(); $this->formOpen = true;
    }
    public function cancel(): void { $this->formOpen = false; $this->resetValidation(); }

    /** Close on Escape or X. Complete forms persist; incomplete drafts are discarded. */
    public function closeForm(UserManager $manager): void
    {
        $this->cancel();
    }
    public function save(UserManager $manager): void
    {
        Gate::authorize('users.manage');
        $data = [
            'name'=>$this->name, 'email'=>$this->email, 'institution_id'=>$this->institution_id,
            'identification'=>$this->identification, 'phone'=>$this->phone, 'job_title'=>$this->job_title,
            'password'=>$this->password, 'is_active'=>$this->is_active, 'can_access_admin'=>$this->can_access_admin,
            'role_ids'=>array_map('intval',$this->role_ids),
        ];
        $target = $this->editingId ? User::findOrFail($this->editingId) : null;
        $manager->save(auth()->user(), $data, $target);
        $this->formOpen = false;
        session()->flash('success','Usuario guardado correctamente.');
    }
    protected function viewModule(): string { return 'users'; }
    protected function viewFields(): array { return ['id' => 'ID', 'name' => 'Usuario', 'email' => 'Correo electrónico', 'institution' => 'Institución', 'institution_id' => 'ID institución', 'job_title' => 'Cargo', 'identification' => 'Identificación', 'phone' => 'Teléfono', 'roles' => 'Roles', 'status' => 'Estado', 'can_access_admin' => 'Acceso administrativo', 'email_verified_at' => 'Correo verificado', 'theme_preference' => 'Tema', 'created_at' => 'Creado el', 'updated_at' => 'Actualizado el']; }
    protected function defaultViews(): array { return [
        ['name' => 'Todos', 'filter' => ''],
        ['name' => 'Activos', 'filter' => '1'],
        ['name' => 'Inactivos', 'filter' => '0'],
    ]; }
    protected function applyViewFilter(string $value): void { $this->activeFilter = $value; }
    public function render()
    {
        Gate::authorize('users.manage');
        $items = User::query()->with(['institution','roles'])
            ->when($this->search, fn($q) => $q->where(fn($x) => $x->where('name','like','%'.$this->search.'%')->orWhere('email','like','%'.$this->search.'%')))
            ->when($this->institutionFilter, fn($q) => $q->where('institution_id',$this->institutionFilter))
            ->when($this->activeFilter !== '', fn($q) => $q->where('is_active',(int)$this->activeFilter))
            ->orderBy('name')->get();
        $institutions = Institution::orderBy('name')->get(['id','name']);
        $roles = Role::where('guard_name','web')->orderBy('name')->get(['id','name']);
        return view('livewire.operations.users',array_merge(compact('items','institutions','roles'), $this->operationViewsData()))->layout('components.layouts.app');
    }
}
