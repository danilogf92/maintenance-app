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

    public function label(string $column, string $value): string
    {
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

    public function prepare(Collection $records, array $columns, array $settings): array
    {
        $settings = $this->normalize($settings, $columns);
        $options = [];
        foreach ($columns as $key => $definition) {
            if ($definition['kind'] === 'category' && $definition['filterable']) {
                $options[$key] = $records->map(fn ($record) => $this->value($record, $key, $definition))
                    ->unique()->sort()->values()->map(fn ($value) => [
                        'value' => $value === '' ? '__empty__' : $value,
                        'label' => $this->label($key, $value),
                    ])->all();
            }
        }
        $items = $records->filter(function ($record) use ($settings, $columns) {
            foreach ($settings['filters'] as $key => $filter) {
                $value = $this->value($record, $key, $columns[$key]);
                $exact = in_array($columns[$key]['kind'], ['category', 'number'], true);
                if ($filter === '__empty__' ? $value !== '' : ($exact
                    ? $value !== $filter : ! str_contains(mb_strtolower($value), mb_strtolower($filter)))) {
                    return false;
                }
            }

            return true;
        });
        if ($settings['sort'] !== '') {
            $key = $settings['sort'];
            $items = $items->sort(function ($a, $b) use ($key, $columns, $settings) {
                $left = $this->value($a, $key, $columns[$key]);
                $right = $this->value($b, $key, $columns[$key]);
                $comparison = $columns[$key]['kind'] === 'number'
                    ? ((float) $left <=> (float) $right) : strnatcasecmp($left, $right);

                return ($settings['direction'] === 'desc' ? -$comparison : $comparison) ?: ($a->id <=> $b->id);
            });
        }
        $groups = [['key' => '', 'label' => null, 'items' => $items->values()]];
        if ($settings['group'] !== '') {
            $key = $settings['group'];
            $grouped = $items->groupBy(fn ($record) => $this->value($record, $key, $columns[$key]));
            $grouped = $settings['sort'] === $key && $settings['direction'] === 'desc'
                ? $grouped->sortKeysDesc(SORT_NATURAL | SORT_FLAG_CASE)
                : $grouped->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);
            $groups = $grouped->map(fn ($rows, $value) => [
                'key' => (string) $value, 'label' => $this->label($key, (string) $value), 'items' => $rows->values(),
            ])->values()->all();
            $items = collect($groups)->flatMap(fn ($group) => $group['items']);
        }

        return ['items' => $items->values(), 'tableGroups' => $groups,
            'tableColumns' => $columns, 'columnOptions' => $options];
    }
}
