@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información de '.$definition['single'], 'editorLabel' => $definition['single'], 'editorPrefix' => 'catalog', 'editorFieldsView' => 'catalogs::field',
 'canViewRelation' => false, 'editorRelationComponent' => '', 'editorRelationKey' => '',
 'editorDraftMessage' => '', 'editorHint' => 'Completa los datos obligatorios para guardar. Puedes inactivar un registro para conservar sus relaciones.',
])
