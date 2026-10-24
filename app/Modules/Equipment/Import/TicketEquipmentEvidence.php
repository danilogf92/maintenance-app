<?php
namespace App\Modules\Equipment\Import;
use App\Modules\Equipment\Models\ReceivedEquipment;
class TicketEquipmentEvidence
{
    private $equipment;
    private array $reverse=[];
    public function __construct()
    {
        $this->equipment=ReceivedEquipment::where('source_table','C')->get()->keyBy('source_id');
        foreach ($this->equipment as $item) {
            foreach ($item->payload['tickets'] ?? [] as $id) { $this->reverse[(string)$id][(string)$item->source_id]=true; }
        }
    }
    public function resolve($ticket): array
    {
        $id=$ticket->payload['value_16'] ?? null; $claims=$this->reverse[(string)$ticket->ninox_id] ?? [];
        if (!$id) { return ['reason'=>'tickets_without_reference']; }
        if (!isset($this->equipment[(string)$id])) { return ['reason'=>'tickets_pending']; }
        if (count($claims)!==1 || !isset($claims[(string)$id])) { return ['reason'=>'conflict','claims'=>array_keys($claims)]; }
        return ['reason'=>'ready','target'=>$this->equipment[(string)$id]->equipment_id];
    }
}
