<?php

namespace App\Modules\Clients\UI;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedEquipment extends Component
{
    use WithPagination;
    #[Locked] public int $clientId;
    public string $search = '';

    public function boot(): void { Permissions::authorize('clients'); Permissions::authorize('equipment'); }
    public function mount(int $clientId): void { $this->clientId = $clientId; VisibleRecords::query('clients')->findOrFail($clientId); }
    public function updatedSearch(): void { $this->resetPage('clientEquipmentPage'); }

    public function render()
    {
        VisibleRecords::query('clients')->findOrFail($this->clientId);
        $items = VisibleRecords::query('equipment')->where('client_id', $this->clientId)->with('institution')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($match) => $match
                ->where('name', 'like', '%'.$this->search.'%')->orWhere('code', 'like', '%'.$this->search.'%')->orWhere('serial', 'like', '%'.$this->search.'%')))
            ->orderBy('name')->paginate(15, ['*'], 'clientEquipmentPage');
        return view('clients::Relations.equipment', compact('items'));
    }
}
