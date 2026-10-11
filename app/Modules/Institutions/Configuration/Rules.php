<?php

namespace App\Modules\Institutions\Configuration;

use App\Models\Institution;
use Illuminate\Validation\Rule;

class Rules
{
    public static function validation(?Institution $record = null): array
    {
        $fields = Fields::definitions();
        $rules = [];
        foreach ($fields as $key => $field) {
            $rules[$key] = [$field['required'] ? 'required' : 'nullable',
                $field['type'] === 'email' ? 'email' : 'string'];
            if (isset($field['max'])) {
                $rules[$key][] = 'max:'.$field['max'];
            }
            if ($field['type'] === 'select') {
                $rules[$key][] = Rule::in(array_keys($field['options']));
            }
        }
        $rules['code'][] = Rule::unique('institutions', 'code')->ignore($record);

        return $rules;
    }
}
