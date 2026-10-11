<?php

namespace App\Modules\Institutions\UI;

use App\Modules\Institutions\Configuration\Relations;
use App\Modules\Institutions\Queries\InstitutionUsersQuery;
use App\Modules\User\UserAccess;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedUsers extends Component
{
    use WithPagination;

    #[Locked]
    public int $institutionId;

    #[Locked]
    public ?int $relatedUserId = null;

    #[Locked]
    public string $childSession = '';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage('institutionUsersPage');
    }

    public function openUser(int $id): void
    {
        abort_unless(in_array('open', Relations::definitions()['users']['actions'], true), 403);
        $user = app(InstitutionUsersQuery::class)->build($this->institutionId)->findOrFail($id);
        app(UserAccess::class)->assertEditable(auth()->user(), $user);
        $this->relatedUserId = $id;
        $this->childSession = (string) Str::uuid();
    }

    #[On('institution-related-user-closed')]
    public function closeUser(): void
    {
        $this->relatedUserId = null;
        $this->childSession = '';
    }

    public function render()
    {
        $definition = Relations::definitions()['users'];
        $columns = array_unique(['id', ...$definition['columns']]);
        if (array_diff($columns, ['id', 'name', 'email', 'job_title', 'phone', 'identification'])) {
            throw new \LogicException('Columna no permitida en Relations.php para usuarios.');
        }
        $items = app(InstitutionUsersQuery::class)->build($this->institutionId, $this->search)
            ->paginate($definition['per_page'], $columns, 'institutionUsersPage');

        return view('institutions::Relations.users', compact('items', 'definition'));
    }
}
