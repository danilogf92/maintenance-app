<?php
namespace App\Modules\Tickets\Import;
use App\Modules\Tickets\Models\{Ticket,NinoxTicketImport};
use Illuminate\Support\Facades\DB;
class ImportTicket
{
    public function execute(array $source): string
    {
        return DB::transaction(function () use ($source) {
            $hash=hash('sha256',json_encode($source,JSON_THROW_ON_ERROR));
            $identity=['ninox_table'=>$source['ninox_table'],'ninox_id'=>$source['ninox_id']];
            $import=NinoxTicketImport::where($identity)->lockForUpdate()->first();
            if ($import) { return $import->source_hash===$hash && $import->ticket()->exists() ? 'existing' : 'conflicts'; }
            if (Ticket::where('code',$source['ticket_code'])->exists()) { return 'conflicts'; }
            $ticket=Ticket::create(['code'=>$source['ticket_code'],'subject'=>$source['report_client'] ?: $source['ticket_code'],
                'description'=>$source['technical_info'] ?? null,'equipment_id'=>null,'technician_id'=>null,
                'opened_at'=>$source['ticket_date'].' 00:00:00','priority'=>'unknown','status'=>'historical','origin'=>'legacy',
                'ninox_status'=>$source['status']]);
            NinoxTicketImport::create([...$identity,'ticket_id'=>$ticket->id,'source_hash'=>$hash,'payload'=>$source]);
            return 'created';
        });
    }
}
