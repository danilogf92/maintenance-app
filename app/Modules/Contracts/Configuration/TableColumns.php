<?php

namespace App\Modules\Contracts\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        $columns['status']['field'] = 'active';
        $columns['status']['kind'] = 'category';
        $columns['status']['groupable'] = true;
        $columns['client']['field'] = 'client.name';
        $columns['equipment']['field'] = 'coverages_count';
        $columns['equipment']['kind'] = 'number';

        return $columns;
    }
}
