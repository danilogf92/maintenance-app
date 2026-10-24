<?php

namespace App\Modules\Contacts\UI;

use App\Modules\Contacts\Configuration\{Fields, Tabs, Layout};

class ContactEditor
{
    public static function configuration(): array
    {
        $tabs = Tabs::definitions(); $fields = Fields::definitions();
        $used = collect($tabs)->flatMap(fn ($tab) => collect($tab['sections'] ?? [])->pluck('fields')->flatten())->all();
        if (count($used) !== count(array_unique($used)) || array_diff(array_keys($fields), $used) || array_diff($used, array_keys($fields))) {
            throw new \LogicException('Cada campo de Contactos debe aparecer una vez en Tabs.php.');
        }
        return ['editorTabs' => $tabs, 'editorFields' => $fields, 'editorLayoutStyle' => Layout::styles()];
    }
}
