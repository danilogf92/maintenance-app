<?php
namespace App\Modules\Tickets\Queries;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Workflow\Configuration\Permissions;
class NinoxTicketDetails
{
    public static function get(?int $id): array
    {
        if (!$id) { return []; }
        $ticket=VisibleRecords::query('tickets')->findOrFail($id); $import=$ticket->ninoxImport;
        if (!$import) { return []; }
        return ['engineers'=>\App\Modules\FieldEngineers\Configuration\Permissions::allows() ? $ticket->fieldEngineers : collect(), 'source'=>$import->payload,'equipment_resolved'=>(bool)$ticket->equipment_id,'technician_resolved'=>(bool)$ticket->technician_id,
            'services'=>Permissions::allows('services') ? VisibleRecords::query('services')->where('ticket_id',$id)->get(['id','code']) : collect()];
    }
}
