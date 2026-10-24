<?php
namespace App\Modules\MaintenanceProtocols\Queries;
use App\Modules\MaintenanceProtocols\Configuration\Permissions;
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolStep};
class ProtocolQuery
{
    public function records(string $search)
    {
        Permissions::authorize();
        return MaintenanceProtocol::query()->withCount('steps')
            ->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('name', 'like', '%'.$search.'%')
                ->orWhere('code', 'like', '%'.$search.'%')->orWhere('model_info', 'like', '%'.$search.'%')))
            ->orderBy('name')->orderBy('id')->get()->each(fn ($p) => $p->setAttribute('status_label',
                $p->is_obsolete === null ? 'Por verificar' : ($p->is_obsolete ? 'Obsoleto' : 'Vigente')));
    }
    public function details(?int $id): ?MaintenanceProtocol
    {
        Permissions::authorize(); return $id ? MaintenanceProtocol::with('steps.files')->findOrFail($id) : null;
    }
    public function pending()
    {
        Permissions::authorize(); return ProtocolStep::with('files')->whereNull('maintenance_protocol_id')->orderBy('parent_source_id')->orderBy('step_number')->orderBy('id')->get();
    }
}
