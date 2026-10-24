@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información de planificación', 'editorLabel' => 'Plan de mantenimiento', 'editorPrefix' => 'maintenance-plan', 'editorFieldsView' => 'maintenance-plans::field',
 'canViewRelation' => true, 'editorRelationComponent' => 'maintenance-plan-visits', 'editorRelationKey' => 'planId',
 'editorDraftMessage' => 'Completa el nombre y el equipo para guardar el plan y programar sus visitas.',
])
