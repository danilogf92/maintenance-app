<?php

namespace App\Modules\Contracts\UI;

use App\Modules\Contracts\Configuration\Fields;
use App\Modules\Contracts\Configuration\Layout;
use App\Modules\Contracts\Configuration\Relations;
use App\Modules\Contracts\Configuration\Tabs;

class ContractEditor
{
    public static function configuration(): array
    {
        $tabs = Tabs::definitions();
        $fields = Fields::definitions();
        $used = collect($tabs)->flatMap(fn ($tab) => collect($tab['sections'] ?? [])->pluck('fields')->flatten())->all();
        if (count($used) !== count(array_unique($used)) || array_diff(array_keys($fields), $used) || array_diff($used, array_keys($fields))) {
            throw new \LogicException('Cada campo de Contratos debe aparecer una vez en Tabs.php.');
        }

        return ['editorRelations' => Relations::definitions(), 'editorTabs' => $tabs, 'editorFields' => $fields, 'editorLayoutStyle' => Layout::styles()];
    }
}
