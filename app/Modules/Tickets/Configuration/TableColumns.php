<?php

namespace App\Modules\Tickets\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        $columns['origin'] = [...$columns['origin'], 'kind' => 'category', 'groupable' => true, 'labels' => Origins::all()];
        $columns['equipment']['field'] = 'equipment.code'; $columns['technician']['field'] = 'technician_label';
        foreach (['status', 'priority', 'equipment', 'technician'] as $key) {
            $columns[$key]['kind'] = 'category'; $columns[$key]['groupable'] = true;
        }
        $columns['status']['labels'] = Fields::statuses(); $columns['priority']['labels'] = Fields::priorities();
        $columns['services'] = [...$columns['services'], 'field' => 'visible_services_count', 'kind' => 'number'];
        $columns['id']['kind'] = 'number';
        foreach (['opened_at', 'created_at', 'updated_at'] as $key) { $columns[$key]['kind'] = 'date'; }
        return $columns;
    }
}
