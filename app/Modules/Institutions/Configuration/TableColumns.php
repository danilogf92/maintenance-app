<?php

namespace App\Modules\Institutions\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false,
                'field' => $key, 'kind' => 'text'];
        }
        foreach (['type', 'city'] as $key) {
            $columns[$key]['kind'] = 'category';
            $columns[$key]['groupable'] = true;
        }
        $columns['users']['field'] = 'users_count';
        foreach (['id', 'users'] as $key) {
            $columns[$key]['kind'] = 'number';
        }
        foreach (['created_at', 'updated_at'] as $key) {
            $columns[$key]['kind'] = 'date';
        }

        return $columns;
    }
}
