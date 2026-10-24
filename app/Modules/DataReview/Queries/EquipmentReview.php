<?php
namespace App\Modules\DataReview\Queries;
use App\Modules\Equipment\Models\ReceivedEquipment;
use App\Modules\Equipment\Import\TicketEquipmentEvidence;
use App\Modules\Tickets\Models\NinoxTicketImport;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
class EquipmentReview
{
    public function execute(): array
    {
        $items=ReceivedEquipment::with('equipment')->get(); $tickets=NinoxTicketImport::with('ticket')->get();
        $catalogs=NinoxCatalogImport::whereIn('catalog',['brands','equipment_models'])->get();
        $modelIds=$catalogs->where('catalog','equipment_models')->pluck('ninox_id')->all();
        $brandIds=$catalogs->where('catalog','brands')->pluck('ninox_id')->all();
        $known=$items->pluck('source_id')->map(fn($id)=>(string)$id)->all(); $catalogEquipment=[];
        foreach ($catalogs as $catalog) {
            foreach ($catalog->payload[$catalog->catalog==='brands' ? 'equipment_id' : 'equipments'] ?? [] as $id) {
                if (!in_array((string)$id,$known,true)) { $catalogEquipment[(string)$id]=true; }
            }
        }
        $missingModels=[]; $missingBrands=[]; $ticketIds=[]; $histories=['institutions'=>0,'clients'=>0];
        foreach ($items as $item) {
            $data=$item->payload;
            if (!empty($data['model_id']) && !in_array((string)$data['model_id'],$modelIds,true)) {
                $missingModels[(string)$data['model_id']][]=['id'=>$item->equipment_id,'serial'=>$item->equipment->serial];
            }
            if (!empty($data['brand_id']) && !in_array((string)$data['brand_id'],$brandIds,true)) {
                $missingBrands[(string)$data['brand_id']][]=['id'=>$item->equipment_id,'serial'=>$item->equipment->serial];
            }
            foreach ($data['tickets'] ?? [] as $id) { $ticketIds[(string)$id]=true; }
            if (!$item->equipment->institution_id) { $histories['institutions']++; }
            if (!$item->equipment->client_id) { $histories['clients']++; }
        }
        $evidence=new TicketEquipmentEvidence; $conflicts=[];
        foreach ($tickets as $ticket) {
            $match=$evidence->resolve($ticket);
            if ($match['reason']==='conflict' || ($match['reason']==='ready' && $ticket->ticket->equipment_id && (int)$ticket->ticket->equipment_id!==(int)$match['target'])) {
                $conflicts[]=['id'=>$ticket->ticket_id,'code'=>$ticket->ticket->code,'reference'=>$ticket->ninox_id,'equipment'=>$ticket->payload['value_16'],'claims'=>$match['claims'] ?? []];
            }
        }
        return ['catalogEquipmentPending'=>array_keys($catalogEquipment),'count'=>$items->count(),'missingModels'=>$missingModels,'missingBrands'=>$missingBrands,'histories'=>$histories,'conflicts'=>$conflicts,
            'pendingTickets'=>count(array_diff(array_keys($ticketIds),$tickets->pluck('ninox_id')->all())),
            'pendingTicketReferences'=>array_values(array_diff(array_keys($ticketIds),$tickets->pluck('ninox_id')->all())),
            'linkedTickets'=>$tickets->filter(fn($t)=>(bool)$t->ticket->equipment_id)->count()];
    }
}
