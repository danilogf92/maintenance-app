<?php

namespace App\Modules\Institutions\UI;

use App\Livewire\User\UserPage;
use App\Modules\Institutions\Configuration\Permissions;
use App\Modules\Institutions\Queries\InstitutionQuery;
use App\Modules\Institutions\Queries\InstitutionUsersQuery;
use App\Modules\User\UserAccess;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

/** Reuses the User module's fields, authorization, validation and autosave. */
class RelatedUserEditor extends UserPage
{
    #[Locked]
    public int $institutionId;

    public function boot(): void
    {
        parent::boot();
        Gate::authorize(Permissions::MANAGE);
    }

    public function mount(int $institutionId = 0, int $recordId = 0): void
    {
        $this->institutionId = $institutionId;
        $this->edit($recordId);
    }

    public function edit(int $id): void
    {
        app(InstitutionUsersQuery::class)->build($this->institutionId)->findOrFail($id);
        parent::edit($id);
    }

    public function create(): void
    {
        abort(403, 'Esta prueba solo abre usuarios existentes.');
    }

    public function cancel(): void
    {
        parent::cancel();
        $this->dispatch('institution-related-user-closed');
    }

    public function render()
    {
        $institutions = app(InstitutionQuery::class)->options();
        $roles = app(UserAccess::class)->rolesFor(auth()->user())->orderBy('name')->get(['id', 'name']);

        return view('institutions::Relations.user-editor', array_merge(compact('institutions', 'roles'), \App\Modules\Users\UI\UserEditor::configuration()));
    }
}
