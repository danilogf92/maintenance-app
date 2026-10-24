<?php

namespace App\Modules\Shared;

use Illuminate\Support\Collection;

/** Transforms the cached snapshot, including eager-loaded relations, without queries. */
class RecordTable
{
    public function normalize(array $settings, array $columns): array
    {
        $filters = [];
        foreach ((array) ($settings['filters'] ?? []) as $key => $value) {
            if (($columns[$key]['filterable'] ?? false) && is_string($value) && $value !== '') {
                $filters[$key] = mb_substr($value, 0, 200);
            }
        }
        $sort = $settings['sort'] ?? '';
        $group = $settings['group'] ?? '';

        return ['filters' => $filters,
            'sort' => is_string($sort) && ($columns[$sort]['sortable'] ?? false) ? $sort : '',
            'direction' => ($settings['direction'] ?? '') === 'desc' ? 'desc' : 'asc',
            'group' => is_string($group) && ($columns[$group]['groupable'] ?? false) ? $group : ''];
    }

    public function value($record, string $column, array $definition): string
    {
        if ($column === 'roles') {
            return $record->roles->pluck('name')->sort()->implode(', ');
        }
        $value = data_get($record, $definition['field']);
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? '');
    }

    public function label(string $column, string $value, array $labels = []): string
    {
        if (array_key_exists($value, $labels)) { return (string) $labels[$value]; }
        if ($value === '') {
            return 'Sin dato';
        }
        if ($column === 'status') {
            return $value === '1' ? 'Activo' : 'Inactivo';
        }
        if ($column === 'can_access_admin') {
            return $value === '1' ? 'Sí' : 'No';
        }
        if ($column === 'type') {
            return ['company' => 'Empresa', 'hospital' => 'Hospital', 'clinic' => 'Clínica',
                'university' => 'Universidad', 'health_center' => 'Centro médico', 'other' => 'Otro'][$value] ?? $value;
        }

        return $value;
    }

    private function valueResolver(array $columns): \Closure
    {
        $values = [];
        return function ($record, string $column) use (&$values, $columns): string {
            $id = spl_object_id($record);
            return $values[$id][$column] ??= $this->value($record, $column, $columns[$column]);
        };
    }

    private function categoryOptions(Collection $records, array $columns, \Closure $value): array
    {
        $options = [];
        foreach ($columns as $key => $definition) {
            if ($definition['kind'] === 'category' && $definition['filterable']) {
                $options[$key] = $records->map(fn ($record) => $value($record, $key))
                    ->unique()->sort()->values()->map(fn ($item) => [
                        'value' => $item === '' ? '__empty__' : $item,
                        'label' => $this->label($key, $item, $definition['labels'] ?? []),
                    ])->all();
            }
        }
        return $options;
    }

    private function filterRecords(Collection $records, array $columns, array $filters, \Closure $value): Collection
    {
        if (!$filters) { return $records; }
        return $records->filter(function ($record) use ($filters, $columns, $value) {
            foreach ($filters as $key => $filter) {
                $item = $value($record, $key);
                $exact = in_array($columns[$key]['kind'], ['category', 'number'], true);
                if ($filter === '__empty__' ? $item !== '' : ($exact
                    ? $item !== $filter : ! str_contains(mb_strtolower($item), mb_strtolower($filter)))) {
                    return false;
                }
            }
            return true;
        });
    }

    private function sortRecords(Collection $items, array $columns, array $settings, \Closure $value): Collection
    {
        $key = $settings['sort'];
        if ($key === '') { return $items; }
        return $items->sort(function ($a, $b) use ($key, $columns, $settings, $value) {
            $left = $value($a, $key); $right = $value($b, $key);
            $comparison = $columns[$key]['kind'] === 'number'
                ? ((float) $left <=> (float) $right) : strnatcasecmp($left, $right);
            return ($settings['direction'] === 'desc' ? -$comparison : $comparison) ?: ($a->id <=> $b->id);
        });
    }

    private function groupedRecords(Collection $items, array $columns, array $settings, \Closure $value): array
    {
        $key = $settings['group'];
        if ($key === '') { return [['key' => '', 'label' => null, 'items' => $items->values()]]; }
        $grouped = $items->groupBy(fn ($record) => $value($record, $key));
        $grouped = $settings['sort'] === $key && $settings['direction'] === 'desc'
            ? $grouped->sortKeysDesc(SORT_NATURAL | SORT_FLAG_CASE)
            : $grouped->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);
        return $grouped->map(fn ($rows, $item) => [
            'key' => (string) $item, 'label' => $this->label($key, (string) $item, $columns[$key]['labels'] ?? []),
            'items' => $rows->values(),
        ])->values()->all();
    }

    public function prepare(Collection $records, array $columns, array $settings): array
    {
        $settings = $this->normalize($settings, $columns);
        $value = $this->valueResolver($columns);
        $options = $this->categoryOptions($records, $columns, $value);
        $items = $this->filterRecords($records, $columns, $settings['filters'], $value);
        $items = $this->sortRecords($items, $columns, $settings, $value);
        $groups = $this->groupedRecords($items, $columns, $settings, $value);
        if ($settings['group'] !== '') { $items = collect($groups)->flatMap(fn ($group) => $group['items']); }
        return ['items' => $items->values(), 'tableGroups' => $groups,
            'tableColumns' => $columns, 'columnOptions' => $options];
    }
}
