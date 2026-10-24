<?php
namespace App\Modules\FieldEngineers\Import;
use App\Modules\FieldEngineers\Models\FieldEngineer;
use App\Modules\Tickets\Models\NinoxTicketImport;
use Illuminate\Support\Facades\DB;
class LinkEngineerTickets
{
    public function execute(): array
    {
        $index=[];foreach (FieldEngineer::all() as $engineer) {
            foreach ($engineer->source_payload['visits'] ?? [] as $id) { $index[(string)$id][$engineer->id]=true; }
        }
        $counts=['ticket_links_created'=>0,'ticket_links_existing'=>0,'ticket_links_pending'=>0,'ticket_link_conflicts'=>0,'tickets_without_assignment'=>0];
        foreach (NinoxTicketImport::where('ninox_table','F')->get() as $source) {
            $assignments=$source->payload['field_service_engineer'] ?? [];
            if (!$assignments) { $counts['tickets_without_assignment']++; continue; }
            foreach ($assignments as $id) {
                $matches=$index[(string)$id] ?? [];
                if (count($matches)!==1) { $counts[count($matches) ? 'ticket_link_conflicts' : 'ticket_links_pending']++; continue; }
                $engineer=(int)array_key_first($matches);
                $result=DB::transaction(function () use ($source,$id,$engineer) {
                    $source->ticket()->lockForUpdate()->firstOrFail();
                    $query=DB::table('ticket_field_engineer')->where('ticket_id',$source->ticket_id)->where('source_assignment_id',(string)$id);
                    $old=$query->first();
                    if ($old) { return (int)$old->field_engineer_id===$engineer ? 'ticket_links_existing' : 'ticket_link_conflicts'; }
                    $query->insert(['ticket_id'=>$source->ticket_id,'source_assignment_id'=>(string)$id,'field_engineer_id'=>$engineer,'created_at'=>now(),'updated_at'=>now()]);
                    return 'ticket_links_created';
                });$counts[$result]++;
            }
        }
        return $counts;
    }
}
