<?php

namespace App\Modules\Users\UI;

use App\Modules\Users\Configuration\{Fields, Tabs, Layout};
use App\Modules\User\UserData;

class UserEditor
{
    public static function configuration(): array
    {
        $fields = Fields::definitions(); $tabs = Tabs::definitions(); $used = [];
        foreach ($tabs as $tab) {
            foreach ($tab['sections'] as $section) {
                foreach ($section['fields'] as $field) {
                    if (! isset($fields[$field]) || in_array($field, $used, true)) {
                        throw new \LogicException('Campo desconocido o duplicado en Usuarios/Tabs.php: '.$field);
                    }
                    $used[] = $field;
                }
            }
        }
        if (array_diff(UserData::FIELDS, $used) || array_diff($used, UserData::FIELDS)) {
            throw new \LogicException('Cada campo de Usuarios debe aparecer una vez en sus pestañas.');
        }
        return ['editorFields' => $fields, 'editorTabs' => $tabs, 'editorLayoutStyle' => Layout::styles()];
    }
}
