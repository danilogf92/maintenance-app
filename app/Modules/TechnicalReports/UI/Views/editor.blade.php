@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información del informe', 'editorLabel' => 'Informe técnico', 'editorPrefix' => 'technical-report', 'editorFieldsView' => 'technical-reports::field',
 'canViewRelation' => false, 'editorRelationComponent' => '', 'editorRelationKey' => 'serviceId',
 'editorDraftMessage' => '', 'editorHint' => 'Selecciona el servicio, el título y la plantilla. El guardado captura sus datos como una revisión en borrador.',
])
