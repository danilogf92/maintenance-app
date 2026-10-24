<?php

namespace App\Modules\Equipment\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        $columns['client']['field'] = 'client.name'; $columns['institution']['field'] = 'institution.name';
        foreach (['status', 'brand', 'model', 'client', 'institution'] as $key) {
            $columns[$key]['kind'] = 'category'; $columns[$key]['groupable'] = true;
        }
        $columns['status']['labels'] = Fields::statuses();
        $columns['tickets'] = [...$columns['tickets'], 'field' => 'visible_tickets_count', 'kind' => 'number'];
        $columns['id']['kind'] = 'number';
        foreach (['created_at', 'updated_at'] as $key) { $columns[$key]['kind'] = 'date'; }
        return $columns;
    }
}
