<?php
namespace App\Modules\DataReview\Queries;
use App\Modules\FieldEngineers\Models\FieldEngineer;
use App\Modules\Tickets\Models\NinoxTicketImport;
use App\Modules\Services\Models\NinoxServiceImport;
use Illuminate\Support\Facades\DB;
class EngineerReview
{
    public function execute(): array
    {
        $engineers=FieldEngineer::all();$sources=NinoxTicketImport::with('ticket.fieldEngineers')->get();
        $known=[];foreach ($engineers as $engineer) { foreach ($engineer->source_payload['visits'] ?? [] as $id) { $known[(string)$id]=true; } }
        $used=[];$pendingTickets=[];$without=0;
        foreach ($sources as $source) {
            $ids=$source->payload['field_service_engineer'] ?? [];
            if (!$ids) { $without++; }
            foreach ($ids as $id) { $used[(string)$id]=true; }
            if ($ids && !$source->ticket->fieldEngineers->count()) { $pendingTickets[]=['id'=>$source->ticket_id,'code'=>$source->ticket->code]; }
        }
        $services=NinoxServiceImport::with('service')->get();
        return ['count'=>$engineers->count(),'ticketLinks'=>DB::table('ticket_field_engineer')->count(),
            'ticketsLinked'=>$sources->filter(fn($s)=>$s->ticket->fieldEngineers->isNotEmpty())->count(),
            'withoutAssignment'=>$without,'pendingTickets'=>$pendingTickets,
            'servicesLinked'=>$services->filter(fn($s)=>(bool)$s->service->field_engineer_id)->count(),
            'pendingServices'=>$services->filter(fn($s)=>!$s->service->field_engineer_id)->map(fn($s)=>['id'=>$s->service_id,'code'=>$s->service->code])->values()->all(),
            'otherAssignments'=>count(array_diff(array_keys($known),array_keys($used))),
            'withoutEmail'=>$engineers->filter(fn($e)=>!$e->email)->count()];
    }
}
