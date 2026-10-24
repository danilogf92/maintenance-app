<?php

namespace App\Modules\Equipment\UI;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedTickets extends Component
{
    use WithPagination;
    #[Locked] public int $equipmentId;
    public string $search = '';

    public function boot(): void { Permissions::authorize('equipment'); Permissions::authorize('tickets'); }
    public function mount(int $equipmentId): void { $this->equipmentId = $equipmentId; VisibleRecords::query('equipment')->findOrFail($equipmentId); }
    public function updatedSearch(): void { $this->resetPage('equipmentTicketsPage'); }

    public function render()
    {
        VisibleRecords::query('equipment')->findOrFail($this->equipmentId);
        $items = VisibleRecords::query('tickets')->where('equipment_id', $this->equipmentId)->with('technician')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$this->search.'%')->orWhere('subject', 'like', '%'.$this->search.'%')))
            ->latest('opened_at')->paginate(15, ['*'], 'equipmentTicketsPage');
        return view('equipment::Relations.tickets', compact('items'));
    }
}
