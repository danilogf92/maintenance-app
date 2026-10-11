<?php

namespace App\Modules\User;

/** Set filterable/sortable/groupable here; no SQL names come from the browser. */
class UserTableColumns
{
    public static function definitions(): array
    {
        $columns = [];
        foreach (UserDefinition::columns() as $key => $label) {
            $columns[$key] = ['filterable' => true, 'sortable' => true, 'groupable' => false,
                'field' => $key, 'kind' => 'text'];
        }
        foreach (['institution', 'job_title', 'roles', 'status', 'can_access_admin', 'theme_preference'] as $key) {
            $columns[$key]['kind'] = 'category';
            $columns[$key]['groupable'] = true;
        }
        $columns['institution']['field'] = 'institution.name';
        $columns['status']['field'] = 'is_active';
        foreach (['id', 'institution_id'] as $key) {
            $columns[$key]['kind'] = 'number';
        }
        foreach (['created_at', 'updated_at', 'email_verified_at'] as $key) {
            $columns[$key]['kind'] = 'date';
        }

        return $columns;
    }
}
