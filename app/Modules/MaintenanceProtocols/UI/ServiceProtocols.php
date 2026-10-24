<?php
namespace App\Modules\MaintenanceProtocols\UI;
use App\Modules\MaintenanceProtocols\Actions\ApplyProtocol;
use App\Modules\MaintenanceProtocols\Configuration\Permissions;
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolApplication};
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;
use Livewire\Component;
class ServiceProtocols extends Component
{
    #[Locked] public int $serviceId;
    public string $protocolId = '', $search = '';
    #[Locked] public string $feedback = '';
    public function boot(): void { Permissions::authorize(); WorkflowPermissions::authorize('services'); }
    public function mount(int $serviceId): void { $this->serviceId = $serviceId; VisibleRecords::query('services')->findOrFail($serviceId); }
    public function apply(): void
    {
        $this->feedback = ''; $this->validate(['protocolId' => 'required|integer|min:1']);
        $application = app(ApplyProtocol::class)->execute($this->serviceId, (int)$this->protocolId);
        $this->feedback = 'Protocolo aplicado: '.$application->activities()->count().' actividades vinculadas. Completa sus resultados y evidencias en Actividades.';
        $this->resetValidation();
    }
    public function render()
    {
        $service = VisibleRecords::query('services')->findOrFail($this->serviceId);
        $options = MaintenanceProtocol::withCount('steps')->where('is_obsolete', false)
            ->when($this->search !== '', fn ($q) => $q->where(fn ($m) => $m->where('name', 'like', '%'.$this->search.'%')->orWhere('code', 'like', '%'.$this->search.'%')))
            ->orderBy('name')->orderBy('id')->get();
        $preview = filter_var($this->protocolId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            ? MaintenanceProtocol::with('steps.files')->find((int)$this->protocolId) : null;
        $applications = ProtocolApplication::where('service_id', $this->serviceId)->withCount(['activities',
            'activities as results_count' => fn ($q) => $q->whereNotNull('result')->where('result', '<>', '')])->orderBy('id')->get();
        if (WorkflowPermissions::allows('activities')) { $applications->load('activities:id,code,description,result,protocol_application_id'); }
        $canApply = Permissions::allows('apply') && WorkflowPermissions::allows('services', 'manage')
            && WorkflowPermissions::allows('activities', 'manage') && !$service->ninoxImport()->exists()
            && !in_array($service->status, ['completed', 'cancelled'], true);
        return view('maintenance-protocols::service', compact('service', 'options', 'preview', 'applications', 'canApply'));
    }
}
