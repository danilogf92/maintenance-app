@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel'=>'Información del contrato','editorLabel'=>'Contrato de mantenimiento','editorPrefix'=>'contract','editorFieldsView'=>'contracts::field',
 'canViewRelation'=>true,'editorRelationComponent'=>'contract-coverages','editorRelationKey'=>'contractId',
 'editorDraftMessage'=>'Completa el número y nombre para guardar el contrato y añadir sus equipos.',
])
