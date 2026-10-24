<?php
namespace App\Modules\Equipment\Queries;
use App\Modules\Workflow\Queries\VisibleRecords;
class ReceivedEquipmentDetails
{
    public static function get(?int $id): array
    {
        if (!$id) { return []; }
        $equipment=VisibleRecords::query('equipment')->findOrFail($id); $source=$equipment->receivedSource;
        if (!$source) { return []; }
        $histories = [];
        foreach (\App\Modules\DataManagement\Configuration\EquipmentHistories::all() as $entity => $definition) {
            $histories[$entity] = \App\Modules\DataManagement\Models\EquipmentHistory::forEntity($entity)->newQuery()->where('equipment_id', $id)
                ->orderBy('assigned_at')->orderBy('source_id')->get(['source_id', $definition['column'], 'assigned_at', 'date_only', 'issues']);
        }
        return ['source'=>$source->payload,'equipment'=>$equipment,'histories'=>$histories];
    }
}
