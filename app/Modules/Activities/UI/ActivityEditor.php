<?php

namespace App\Modules\Activities\UI;

use App\Modules\Activities\Configuration\{Fields, Tabs, Layout};

class ActivityEditor
{
    public static function configuration(): array
    {
        $tabs = Tabs::definitions(); $fields = Fields::definitions();
        $used = collect($tabs)->flatMap(fn ($tab) => collect($tab['sections'] ?? [])->pluck('fields')->flatten())->all();
        if (count($used) !== count(array_unique($used)) || array_diff(array_keys($fields), $used) || array_diff($used, array_keys($fields))) {
            throw new \LogicException('Cada campo de Actividades debe aparecer una vez en Tabs.php.');
        }
        return ['editorTabs' => $tabs, 'editorFields' => $fields, 'editorLayoutStyle' => Layout::styles()];
    }
}
