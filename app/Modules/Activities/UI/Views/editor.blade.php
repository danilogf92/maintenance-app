@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información de la actividad', 'editorLabel' => 'Actividad', 'editorPrefix' => 'activity', 'editorFieldsView' => 'activities::field',
 'canViewRelation' => false, 'editorRelationComponent' => '', 'editorRelationKey' => 'serviceId',
 'editorDraftMessage' => '', 'editorHint' => 'Selecciona el servicio y completa la actividad para guardar. Después podrás añadir fotografías.',
])
