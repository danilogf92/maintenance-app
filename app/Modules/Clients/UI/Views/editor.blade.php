@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información del cliente', 'editorLabel' => 'Cliente', 'editorPrefix' => 'client', 'editorFieldsView' => 'clients::field',
 'canViewRelation' => $canViewEquipment, 'editorRelationComponent' => 'client-related-equipment',
 'editorRelationKey' => 'clientId', 'editorDraftMessage' => 'Completa el nombre en General para guardar el cliente y ver sus equipos.',
])
