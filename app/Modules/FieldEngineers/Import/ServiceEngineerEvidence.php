<?php
namespace App\Modules\FieldEngineers\Import;
use App\Modules\FieldEngineers\Models\FieldEngineer;
class ServiceEngineerEvidence
{
    private $engineers;
    public function __construct() { $this->engineers=FieldEngineer::all(); }
    public function resolve($source): ?array
    {
        $data=$source->payload; $name=trim($data['engineer_assigned'] ?? ''); if ($name==='') { return null; }
        $matches=$this->engineers->filter(fn($e)=>$e->name===$name); if ($matches->count()!==1) { return null; }
        $engineer=$matches->first();$service=$source->service;
        if ($service->ticket_id && $service->ticket->fieldEngineers->contains('id',$engineer->id)) {
            return ['id'=>$engineer->id,'basis'=>'ticket_assignment_and_name'];
        }
        if (!$service->ticket_id && trim($engineer->phone ?? '')!=='') {
            $snapshot=explode(' / ',$data['technician_assigned'] ?? '',2);
            if (count($snapshot)===2 && trim($snapshot[0])===$name && trim($snapshot[1])===trim($engineer->phone)) {
                return ['id'=>$engineer->id,'basis'=>'registered_name_phone'];
            }
        }
        return null;
    }
}
