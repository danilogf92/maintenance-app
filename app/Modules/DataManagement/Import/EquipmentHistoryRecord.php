<?php
namespace App\Modules\DataManagement\Import;
use App\Modules\DataManagement\Configuration\EquipmentHistories;
use App\Modules\DataManagement\Models\{DataSourceRecord, EquipmentHistory};
use App\Modules\DataManagement\Support\HistoryValue;
use App\Modules\Equipment\Models\ReceivedEquipment;
use Illuminate\Support\Facades\DB;
class EquipmentHistoryRecord
{
    public function execute(string $entity, array $source): array
    {
        $definition = EquipmentHistories::all()[$entity]; $hash = hash('sha256', json_encode($source, JSON_THROW_ON_ERROR));
        $model = EquipmentHistory::forEntity($entity); $existing = $model->newQuery()->where('source_id', (string)$source['ninox_id'])->lockForUpdate()->first();
        if ($existing && $existing->source_hash !== $hash) { return ['status' => 'conflict', 'issues' => ['source_conflict']]; }
        $equipmentSourceId = HistoryValue::single($source[$definition['equipment']] ?? null);
        $parentSourceId = HistoryValue::single($source[$definition['parent']] ?? null);
        $equipment = $equipmentSourceId ? ReceivedEquipment::where('source_table', 'C')->where('source_id', $equipmentSourceId)->first() : null;
        $parent = $parentSourceId ? DataSourceRecord::where('entity', $definition['parent_entity'])->where('source_table', $definition['parent_table'])->where('source_id', $parentSourceId)->first() : null;
        $issues = []; $equipmentId = $equipment && DB::table('equipment')->where('id', $equipment->equipment_id)->exists() ? $equipment->equipment_id : null;
        $parentId = $parent && DB::table($definition['parent_entity'])->where('id', $parent->record_id)->exists() ? $parent->record_id : null;
        if (!$equipmentId) { $issues[] = 'equipment_reference_pending'; }
        if (!$parentId) { $issues[] = 'parent_reference_pending'; }
        $reverse = $equipment ? HistoryValue::references($equipment->payload[$definition['reverse']] ?? null) : [];
        if ($equipmentId && ($reverse === null || !in_array((string)$source['ninox_id'], $reverse, true))) { $issues[] = 'reverse_reference_mismatch'; }
        $date = HistoryValue::date($source[$definition['date']] ?? null);
        if ($date['issue']) { $issues[] = $date['issue']; }
        $record = $existing ?? $model;
        $record->fill(['source_id' => (string)$source['ninox_id'], 'source_hash' => $hash, 'equipment_id' => $equipmentId,
            $definition['column'] => $parentId, 'assigned_at' => $date['value'], 'date_only' => $date['date_only'], 'issues' => $issues]);
        if (!$existing) { $record->payload = $source; }
        $status = !$existing ? 'created' : ($record->isDirty() ? 'updated' : 'existing');
        if ($record->isDirty()) { $record->save(); }
        return ['status' => $status, 'issues' => $issues];
    }
}
