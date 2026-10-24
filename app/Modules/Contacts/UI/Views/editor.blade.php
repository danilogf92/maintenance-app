@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información del contacto', 'editorLabel' => 'Contacto', 'editorPrefix' => 'contact', 'editorFieldsView' => 'contacts::field',
 'canViewRelation' => true, 'editorRelationComponent' => 'contact-links', 'editorRelationKey' => 'contactId',
 'editorDraftMessage' => 'Completa el nombre en General para guardar el contacto y vincularlo.',
])
