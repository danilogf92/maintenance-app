<?php

namespace App\Livewire\User;

use App\Livewire\Shared\RecordEditor;
use App\Models\User;
use App\Modules\Institutions\Queries\InstitutionQuery;
use App\Modules\User\DeleteUser;
use App\Modules\User\UserAccess;
use App\Modules\User\UserData;
use App\Modules\User\UserDefinition;
use App\Modules\User\UserManager;
use App\Modules\User\UserQuery;
use App\Modules\User\UserTableColumns;
use Illuminate\Database\Eloquent\Model;

class UserPage extends RecordEditor
{
    public string $activeFilter = '';

    public string $name = '';

    public string $email = '';

    public string $identification = '';

    public string $phone = '';

    public string $job_title = '';

    public string $password = '';

    public string $institution_id = '';

    public bool $is_active = true;

    public bool $can_access_admin = false;

    public array $role_ids = [];

    protected function modelClass(): string
    {
        return User::class;
    }

    protected function editableFields(): array
    {
        return UserData::FIELDS;
    }

    protected function viewModule(): string
    {
        return 'users';
    }

    protected function viewFields(): array
    {
        return UserDefinition::columns();
    }

    protected function tableColumns(): array
    {
        return UserTableColumns::definitions();
    }

    protected function defaultViews(): array
    {
        return UserDefinition::views();
    }

    protected function applyViewFilter(string $value): void
    {
        $this->activeFilter = $value;
    }

    protected function resetRecord(): void
    {
        $this->reset(UserData::FIELDS);
    }

    protected function fillRecord(Model $record): void
    {
        app(UserAccess::class)->assertEditable(auth()->user(), $record);
        foreach (['name', 'email', 'identification', 'phone', 'job_title', 'institution_id'] as $field) {
            $this->$field = (string) ($record->$field ?? '');
        }
        $this->is_active = (bool) $record->is_active;
        $this->can_access_admin = (bool) $record->can_access_admin;
        $this->role_ids = $record->roles->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->password = '';
    }

    protected function persistRecord(): Model
    {
        $target = $this->editingId ? User::findOrFail($this->editingId) : null;

        return app(UserManager::class)->save(auth()->user(), $this->recordData(), $target);
    }

    protected function removeRecord(int $id): void
    {
        app(DeleteUser::class)->execute(auth()->user(), $id);
    }

    public function render()
    {
        $items = app(UserQuery::class)->records(auth()->id(), $this->search, '', $this->activeFilter);
        $institutions = $this->formOpen ? app(InstitutionQuery::class)->options() : collect();
        $roles = $this->formOpen ? app(UserAccess::class)->rolesFor(auth()->user())->orderBy('name')->get(['id', 'name']) : collect();

        return view('users::page', array_merge(compact('items', 'institutions', 'roles'), $this->operationViewsData(), $this->prepareTable($items), \App\Modules\Users\UI\UserEditor::configuration()))
            ->layout('components.layouts.app');
    }
}
