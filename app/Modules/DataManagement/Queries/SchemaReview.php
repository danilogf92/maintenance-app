<?php
namespace App\Modules\DataManagement\Queries;
use App\Modules\DataManagement\Configuration\Entities;
use Illuminate\Support\Facades\{Cache, DB, Schema};
class SchemaReview
{
    public function metadata(): array
    {
        $key = 'data-management-schema-11.64.0-'.hash('sha256', (string)config('database.default').':'.config('database.connections.'.config('database.default').'.database'));
        return Cache::remember($key, 3600, function () {
            $result = [];
            foreach (Entities::all() as $table => $label) {
                if (!Schema::hasTable($table)) { continue; }
                $result[$table] = ['label' => $label, 'columns' => Schema::getColumns($table), 'foreign_keys' => Schema::getForeignKeys($table)];
            }
            return $result;
        });
    }
    public function counts(?array $tables = null): array
    {
        $counts = [];
        foreach (array_keys($this->metadata()) as $table) { if ($tables !== null && !in_array($table, $tables, true)) { continue; } $counts[$table] = DB::table($table)->count(); }
        return $counts;
    }
    public function relations(array $sources): array
    {
        $rows = [];
        foreach ($this->metadata() as $table => $definition) {
            $columns = collect($definition['columns'])->keyBy('name');
            foreach ($definition['foreign_keys'] as $key) {
                if (count($key['columns']) !== 1 || count($key['foreign_columns']) !== 1) { continue; }
                $column = $key['columns'][0]; $parent = $key['foreign_table'];
                if ($column === 'created_by') { continue; }
                $stats = DB::table($table)->selectRaw('COUNT(*) as total')->selectRaw('COUNT('.DB::connection()->getQueryGrammar()->wrap($column).') as linked')->first();
                $rows[] = ['child' => $table, 'parent' => $parent, 'column' => $column,
                    'child_label' => Entities::label($table), 'parent_label' => Entities::label($parent),
                    'nullable' => $columns[$column]['nullable'] ?? true, 'total' => (int)$stats->total,
                    'linked' => (int)$stats->linked, 'unlinked' => (int)$stats->total - (int)$stats->linked,
                    'source_parent_present' => in_array($parent, $sources, true)];
            }
        }
        return $rows;
    }
}
