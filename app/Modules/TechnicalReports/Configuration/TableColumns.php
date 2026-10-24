<?php
namespace App\Modules\TechnicalReports\Configuration;

class TableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (Columns::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false, 'field' => $key, 'kind' => 'text'];
        }
        foreach (['service' => 'service.code', 'template' => 'template.name'] as $key => $field) {
            $columns[$key]['field'] = $field; $columns[$key]['kind'] = 'category'; $columns[$key]['groupable'] = true;
        }
        foreach (['id', 'revision'] as $key) { $columns[$key]['kind'] = 'number'; }
        foreach (['created_at', 'updated_at'] as $key) { $columns[$key]['kind'] = 'date'; }
        return $columns;
    }
}
