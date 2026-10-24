<?php
namespace App\Modules\Tickets\Import;
use App\Modules\Tickets\Models\NinoxTicketImport;
use App\Modules\Services\Models\NinoxServiceImport;
use Illuminate\Support\Facades\DB;
class LinkNinoxServices
{
    public function execute(): array
    {
        $tickets=NinoxTicketImport::where('ninox_table','F')->get()->keyBy('ninox_id');
        $counts=['services_linked'=>0,'services_already_linked'=>0,'services_without_source_ticket'=>0,'services_pending'=>0,'link_conflicts'=>0];
        foreach (NinoxServiceImport::where('ninox_table','K')->get() as $import) {
            $source=$import->payload; $id=$source['tickets_associated'] ?? null;
            if (!$id) { $counts['services_without_source_ticket']++; continue; }
            $ticket=$tickets->get((string)$id);
            if (!$ticket) { $counts['services_pending']++; continue; }
            if (!in_array((int)$import->ninox_id,$ticket->payload['value_15'] ?? [],true)) { $counts['link_conflicts']++; continue; }
            $result=DB::transaction(function () use ($import,$ticket) {
                $service=$import->service()->lockForUpdate()->firstOrFail();
                if ($service->ticket_id) { return (int)$service->ticket_id===(int)$ticket->ticket_id ? 'services_already_linked' : 'link_conflicts'; }
                $service->ticket_id=$ticket->ticket_id; $service->lock_version++; $service->save();
                return 'services_linked';
            });
            $counts[$result]++;
        }
        return $counts;
    }
}
