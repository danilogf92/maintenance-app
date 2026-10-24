<?php

namespace App\Modules\Services\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        foreach (['ticket' => 'ticket.code', 'equipment' => 'ticket.equipment.code', 'technician' => 'technician_label'] as $key => $field) {
            $columns[$key]['field'] = $field;
        }
        foreach (['status', 'service_type', 'ticket', 'equipment', 'technician'] as $key) {
            $columns[$key]['kind'] = 'category'; $columns[$key]['groupable'] = true;
        }
        $columns['status']['labels'] = Fields::statuses(); $columns['id']['kind'] = 'number';
        foreach (['activities', 'reports'] as $key) { $columns[$key]['field'] = $key.'_count'; $columns[$key]['kind'] = 'number'; }
        foreach (['service_date', 'created_at', 'updated_at'] as $key) { $columns[$key]['kind'] = 'date'; }
        return $columns;
    }
}
