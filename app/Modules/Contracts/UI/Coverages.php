<?php

namespace App\Modules\Contracts\UI;

use App\Modules\Contracts\Actions\SaveCoverage;
use App\Modules\Contracts\Configuration\Permissions;
use App\Modules\Contracts\Queries\Compliance;
use App\Modules\Contracts\Queries\ContractQuery;
use App\Modules\Tickets\Queries\TicketOptions;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Coverages extends Component
{
    #[Locked]
    public int $contractId;

    #[Locked]
    public int $version = 0;

    #[Locked]
    public ?int $coverageId = null;

    public bool $showForm = false;

    public string $equipment_id = '';

    public string $first_month = '';

    public string $end_month = '';

    public string $requirements = '';

    public string $equipmentSearch = '';

    public string $notice = '';

    public function boot(): void
    {
        Permissions::authorize();
    }

    public function mount(int $contractId): void
    {
        $this->contractId = $contractId;
        ContractQuery::scoped()->findOrFail($contractId);
    }

    public function create(): void
    {
        Permissions::authorize('manage');
        $this->reset('coverageId', 'version', 'equipment_id', 'first_month', 'end_month', 'requirements', 'equipmentSearch');
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Permissions::authorize('manage');
        $row = ContractQuery::scoped()->findOrFail($this->contractId)->coverages()->findOrFail($id);
        $this->coverageId = $id;
        $this->version = $row->lock_version;
        foreach (['equipment_id', 'first_month', 'end_month', 'requirements'] as $field) {
            $this->$field = (string) $row->$field;
        }
        $this->showForm = true;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        app(SaveCoverage::class)->execute($this->contractId, ['equipment_id' => $this->equipment_id, 'first_month' => $this->first_month,
            'end_month' => $this->end_month, 'requirements' => $this->requirements, 'lock_version' => $this->version], $this->coverageId);
        $this->cancel();
        $this->notice = 'Cobertura guardada. La frecuencia se configura en los planes de este equipo.';
    }

    public function render()
    {
        $contract = ContractQuery::scoped()->findOrFail($this->contractId);
        $coverages = app(Compliance::class)->forContract($this->contractId);
        $equipmentOptions = $this->showForm ? TicketOptions::equipment($this->equipmentSearch, $this->equipment_id) : [];

        return view('contracts::coverages', compact('contract', 'coverages', 'equipmentOptions') + ['canManage' => Permissions::allows('manage')]);
    }
}
