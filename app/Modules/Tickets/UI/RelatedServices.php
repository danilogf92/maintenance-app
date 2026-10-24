<?php

namespace App\Modules\Tickets\UI;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class RelatedServices extends Component
{
    use WithPagination;
    #[Locked] public int $ticketId;
    public string $search = '';

    public function boot(): void { Permissions::authorize('tickets'); Permissions::authorize('services'); }
    public function mount(int $ticketId): void { $this->ticketId = $ticketId; VisibleRecords::query('tickets')->findOrFail($ticketId); }
    public function updatedSearch(): void { $this->resetPage('ticketServicesPage'); }

    public function render()
    {
        VisibleRecords::query('tickets')->findOrFail($this->ticketId);
        $items = VisibleRecords::query('services')->where('ticket_id', $this->ticketId)->with('technician')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($m) => $m->where('code', 'like', '%'.$this->search.'%')->orWhere('service_type', 'like', '%'.$this->search.'%')))
            ->latest('service_date')->paginate(15, ['*'], 'ticketServicesPage');
        return view('tickets::Relations.services', compact('items'));
    }
}
