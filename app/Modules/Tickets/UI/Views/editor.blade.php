@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información del ticket', 'editorLabel' => 'Ticket', 'editorPrefix' => 'ticket', 'editorFieldsView' => 'tickets::field',
 'canViewRelation' => $canViewServices, 'editorRelationComponent' => 'ticket-related-services',
 'editorRelationKey' => 'ticketId', 'editorDraftMessage' => 'Completa el asunto y la asignación para guardar el ticket y ver sus servicios.',
 'editorHint' => 'Completa el asunto y selecciona equipo y técnico en Asignación. El código se genera al guardar.',
])
