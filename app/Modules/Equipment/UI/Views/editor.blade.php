@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información del equipo', 'editorLabel' => 'Equipo', 'editorPrefix' => 'equipment', 'editorFieldsView' => 'equipment::field',
 'canViewRelation' => $canViewTickets, 'editorRelationComponent' => 'equipment-related-tickets',
 'editorRelationKey' => 'equipmentId', 'editorDraftMessage' => 'Completa General y Ubicación para guardar el equipo y ver sus tickets.',
 'editorHint' => 'Para crear el equipo completa el nombre y selecciona cliente e institución en Ubicación.',
])
