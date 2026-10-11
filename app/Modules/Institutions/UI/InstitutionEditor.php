<?php

namespace App\Modules\Institutions\UI;

use App\Modules\Institutions\Configuration\Fields;
use App\Modules\Institutions\Configuration\Layout;
use App\Modules\Institutions\Configuration\Relations;
use App\Modules\Institutions\Configuration\Tabs;

/** Editor presentation: tabs and fields are configured without duplicating persistence. */
class InstitutionEditor
{
    public function configuration(?array $tabs = null): array
    {
        $fields = Fields::definitions();
        $relations = Relations::definitions();
        $tabs ??= Tabs::definitions();
        self::validateTabs($tabs, $fields, $relations);

        return ['editorFields' => $fields, 'editorTabs' => $tabs,
            'editorRelations' => $relations, 'editorLayoutStyle' => Layout::styles()];
    }

    private static function validateTabs(array $tabs, array $fields, array $relations): void
    {
        $used = [];
        foreach ($tabs as $tab) {
            if (isset($tab['relation']) && ! isset($relations[$tab['relation']])) {
                throw new \LogicException('Relación desconocida en Tabs.php.');
            }
            foreach ($tab['sections'] ?? [] as $section) {
                foreach ($section['fields'] as $field) {
                    if (! isset($fields[$field]) || in_array($field, $used, true)) {
                        throw new \LogicException('Campo desconocido o duplicado en Tabs.php: '.$field);
                    }
                    $used[] = $field;
                }
            }
        }
        if (array_diff(array_keys($fields), $used)) {
            throw new \LogicException('Cada campo debe estar asignado a una pestaña.');
        }
    }
}
