<?php

namespace App\Modules\Activities\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        foreach (['service' => 'service.code', 'equipment' => 'service.ticket.equipment.code', 'technician' => 'service.technician.name'] as $key => $field) {
            $columns[$key]['field'] = $field; $columns[$key]['kind'] = 'category'; $columns[$key]['groupable'] = true;
        }
        $columns['photos']['field'] = 'photo_count'; $columns['photos']['kind'] = 'number'; $columns['id']['kind'] = 'number';
        foreach (['created_at', 'updated_at'] as $key) { $columns[$key]['kind'] = 'date'; }
        return $columns;
    }
}
