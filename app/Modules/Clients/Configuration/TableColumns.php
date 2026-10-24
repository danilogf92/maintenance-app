<?php

namespace App\Modules\Clients\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        $columns['status'] = [...$columns['status'], 'field' => 'active', 'kind' => 'category', 'groupable' => true];
        $columns['city'] = [...$columns['city'], 'kind' => 'category', 'groupable' => true];
        $columns['equipment'] = [...$columns['equipment'], 'field' => 'equipment_count', 'kind' => 'number'];
        $columns['id']['kind'] = 'number';
        foreach (['created_at', 'updated_at'] as $key) { $columns[$key]['kind'] = 'date'; }
        return $columns;
    }
}
