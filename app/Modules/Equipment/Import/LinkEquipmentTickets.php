<?php
namespace App\Modules\Equipment\Import;
use App\Modules\Tickets\Models\NinoxTicketImport;
use Illuminate\Support\Facades\DB;
class LinkEquipmentTickets
{
    public function execute(): array
    {
        $evidence=new TicketEquipmentEvidence;
        $counts=['tickets_linked'=>0,'tickets_existing'=>0,'tickets_pending'=>0,'tickets_without_reference'=>0,'ticket_conflicts'=>[]];
        foreach (NinoxTicketImport::where('ninox_table','F')->get() as $ticket) {
            $match=$evidence->resolve($ticket); $key=(string)$ticket->ninox_id;
            if ($match['reason']==='conflict') { $counts['ticket_conflicts'][]=$key; continue; }
            if ($match['reason']!=='ready') { $counts[$match['reason']]++; continue; }
            $result=DB::transaction(function () use ($ticket,$match) {
                $record=$ticket->ticket()->lockForUpdate()->firstOrFail(); $target=$match['target'];
                if ($record->equipment_id) { return (int)$record->equipment_id===(int)$target ? 'tickets_existing' : 'conflict'; }
                $record->equipment_id=$target; $record->lock_version++; $record->save(); return 'tickets_linked';
            });
            if ($result==='conflict') { $counts['ticket_conflicts'][]=$key; } else { $counts[$result]++; }
        }
        return $counts;
    }
}
