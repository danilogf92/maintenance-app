<?php

namespace App\Modules\MaintenancePlans\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        $columns['status'] = [...$columns['status'], 'field' => 'active', 'kind' => 'category', 'groupable' => true];
        $columns['equipment']['field'] = 'equipment.code'; $columns['technician']['field'] = 'technician.name';
        $columns['first_month']['kind'] = 'date'; $columns['visits']['field'] = 'visits_count'; $columns['visits']['kind'] = 'number';
        $columns['origin']['field']='planning_origin'; $columns['origin']['kind']='category'; $columns['origin']['groupable']=true;
        return $columns;
    }
}
