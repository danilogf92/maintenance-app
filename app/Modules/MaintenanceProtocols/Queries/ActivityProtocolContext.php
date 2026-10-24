<?php
namespace App\Modules\MaintenanceProtocols\Queries;
use App\Modules\MaintenanceProtocols\Configuration\Permissions;
use App\Modules\MaintenanceProtocols\Models\ProtocolStep;
use App\Modules\Workflow\Queries\VisibleRecords;
class ActivityProtocolContext
{
    public static function forActivity(?int $id): array
    {
        if (!$id || !Permissions::allows()) { return []; }
        $activity = VisibleRecords::query('activities')->findOrFail($id);
        if (!$activity->protocol_application_id) { return []; }
        return ['snapshot' => $activity->protocol_snapshot ?? [],
            'files' => ProtocolStep::find($activity->protocol_step_id)?->files()->get() ?? collect()];
    }
}
