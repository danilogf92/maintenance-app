<?php
namespace App\Modules\MaintenanceProtocols\Queries;
use App\Modules\MaintenanceProtocols\Configuration\Permissions;
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolStep, ProtocolFile};
class ProtocolReview
{
    public function execute(): array
    {
        if (!Permissions::allows()) { return []; }
        return ['protocols' => MaintenanceProtocol::count(), 'steps' => ProtocolStep::count(), 'files' => ProtocolFile::count(),
            'withoutParent' => ProtocolStep::whereNull('maintenance_protocol_id')->count(),
            'parents' => ProtocolStep::whereNull('maintenance_protocol_id')->whereNotNull('parent_source_id')->distinct()->pluck('parent_source_id')->all(),
            'withoutReference' => ProtocolStep::whereNull('maintenance_protocol_id')->whereNull('parent_source_id')->count(),
            'emptyInstructions' => ProtocolStep::with('protocol')->whereNotNull('maintenance_protocol_id')
                ->where(fn ($q) => $q->whereNull('instruction')->orWhere('instruction', ''))
                ->where(fn ($q) => $q->whereNull('report_text')->orWhere('report_text', ''))->get()];
    }
}
