<?php

namespace App\Modules\Services\UI;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class RelatedServiceRecords extends Component
{
    #[Locked] public int $serviceId;
    public string $search = '';

    abstract protected function module(): string;
    abstract protected function fields(): array;
    abstract protected function searchField(): string;
    abstract protected function viewName(): string;

    public function boot(): void { Permissions::authorize('services'); Permissions::authorize($this->module()); }
    public function mount(int $serviceId): void { $this->serviceId = $serviceId; VisibleRecords::query('services')->findOrFail($serviceId); }

    public function render()
    {
        VisibleRecords::query('services')->findOrFail($this->serviceId);
        $field = $this->searchField();
        $items = VisibleRecords::query($this->module())->where('service_id', $this->serviceId)->select($this->fields())
            ->when($this->search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$this->search.'%')->orWhere($field, 'like', '%'.$this->search.'%')))
            ->latest('created_at')->orderByDesc('id')->get();
        return view($this->viewName(), compact('items'));
    }
}
