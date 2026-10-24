<?php
namespace App\Modules\MaintenancePlans\UI;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\{Component, Attributes\Locked};
class EquipmentPlans extends Component
{
    #[Locked] public int $equipmentId;
    public function boot(): void { Permissions::authorize(); }
    public function mount(int $equipmentId): void { $this->equipmentId = $equipmentId; }
    public function render()
    {
        VisibleRecords::query('equipment')->findOrFail($this->equipmentId);
        return view('maintenance-plans::equipment-plans', [
            'plans' => PlanQuery::scoped()->where('equipment_id', $this->equipmentId)->withCount('visits')->orderBy('first_month')->get(),
            'canManage' => Permissions::allows('manage')]);
    }
}
