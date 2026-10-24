<?php
namespace App\Modules\DataManagement\Actions;
use App\Modules\DataManagement\Configuration\EquipmentHistories;
use App\Modules\DataManagement\Models\EquipmentHistory;
use App\Modules\DataManagement\Support\HistoryValue;
use App\Modules\Equipment\Models\ReceivedEquipment;
use Illuminate\Support\Facades\DB;
class ResolveEquipmentAssignments
{
    public function execute(array $snapshot, array $conflicts): array
    {
        $definitions = EquipmentHistories::all(); $stored = []; $claims = [];
        foreach ($definitions as $entity => $definition) {
            $stored[$entity] = EquipmentHistory::forEntity($entity)->newQuery()->get(['source_id', 'equipment_id', $definition['column'], 'assigned_at', 'date_only', 'issues'])->keyBy('source_id');
            foreach ($snapshot[$entity] as $id => $source) {
                foreach (HistoryValue::references($source[$definition['equipment']] ?? null) ?? [] as $equipmentSourceId) { $claims[$entity][$equipmentSourceId][] = (string)$id; }
            }
        }
        $results = [];
        foreach (ReceivedEquipment::where('source_table', 'C')->orderBy('equipment_id')->get() as $received) {
            $equipment = DB::table('equipment')->where('id', $received->equipment_id)->lockForUpdate()->first();
            if (!$equipment) { continue; }
            $payload = $received->payload;
            foreach ($definitions as $entity => $definition) {
                $base = ['equipment_id' => $equipment->id, 'source_id' => $received->source_id, 'entity' => $entity, 'column' => $definition['column']];
                $ids = HistoryValue::references($payload[$definition['reverse']] ?? null);
                $claimed = $claims[$entity][(string)$received->source_id] ?? [];
                if ($ids === null || !$ids || array_diff($ids, $claimed) || array_diff($claimed, $ids)) {
                    $results[] = $base + ['status' => 'pending', 'reason' => 'history_references_incomplete', 'history_ids' => $ids ?? []]; continue;
                }
                if (array_intersect($ids, $conflicts[$entity] ?? [])) {
                    $results[] = $base + ['status' => 'conflict', 'reason' => 'source_conflict', 'history_ids' => $ids]; continue;
                }
                $histories = [];
                foreach ($ids as $id) {
                    $row = $stored[$entity]->get($id);
                    if (!$row || (int)$row->equipment_id !== (int)$equipment->id) { $histories = []; break; }
                    $histories[] = $row;
                }
                $target = $this->choose($histories, $definition['column']);
                if ($target === null) { $results[] = $base + ['status' => 'pending', 'reason' => 'assignment_not_determined', 'history_ids' => $ids]; continue; }
                $current = $equipment->{$definition['column']};
                if ($current !== null && (int)$current !== $target) {
                    $results[] = $base + ['status' => 'conflict', 'reason' => 'existing_link_conflict', 'history_ids' => $ids]; continue;
                }
                $status = $current === null ? 'linked' : 'existing';
                if ($status === 'linked') {
                    DB::table('equipment')->where('id', $equipment->id)->whereNull($definition['column'])->update([
                        $definition['column'] => $target, 'lock_version' => DB::raw('lock_version + 1'), 'updated_at' => now()]);
                    $equipment->{$definition['column']} = $target;
                }
                $results[] = $base + ['status' => $status, 'reason' => 'verified', 'target_id' => $target, 'history_ids' => $ids];
            }
        }
        return $results;
    }
    private function choose(array $histories, string $column): ?int
    {
        if (!$histories) { return null; }
        foreach ($histories as $history) {
            if (!$history->$column || array_diff($history->issues, ['date_missing'])) { return null; }
        }
        $now = now('America/Guayaquil');
        $active = array_values(array_filter($histories, fn ($h) => $h->assigned_at === null || ($h->date_only
            ? substr($h->assigned_at, 0, 10) <= $now->format('Y-m-d') : $h->assigned_at <= $now->format('Y-m-d H:i:s'))));
        if (!$active) { return null; }
        $targets = array_unique(array_map(fn ($h) => (int)$h->$column, $active));
        if (count($targets) === 1) { return (int)reset($targets); }
        if (array_filter($active, fn ($h) => $h->assigned_at === null)) { return null; }
        $lastDay = max(array_map(fn ($h) => substr($h->assigned_at, 0, 10), $active));
        $last = array_values(array_filter($active, fn ($h) => substr($h->assigned_at, 0, 10) === $lastDay));
        if (array_filter($last, fn ($h) => $h->date_only)) {
            $targets = array_unique(array_map(fn ($h) => (int)$h->$column, $last));
        } else {
            $latest = max(array_map(fn ($h) => $h->assigned_at, $last));
            $targets = array_unique(array_map(fn ($h) => (int)$h->$column, array_filter($last, fn ($h) => $h->assigned_at === $latest)));
        }
        return count($targets) === 1 ? (int)reset($targets) : null;
    }
}
