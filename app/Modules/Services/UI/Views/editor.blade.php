@include('components.record-editor.tabbed-detail', [
 'editorAriaLabel' => 'Información del servicio', 'editorLabel' => 'Servicio', 'editorPrefix' => 'service', 'editorFieldsView' => 'services::field',
 'canViewRelation' => false, 'editorRelationComponent' => '', 'editorRelationKey' => 'serviceId',
 'editorRelations' => [
  'maintenance_protocols' => ['component' => 'service-protocols', 'key' => 'serviceId', 'allowed' => \App\Modules\MaintenanceProtocols\Configuration\Permissions::allows()],
  'activities' => ['component' => 'service-related-activities', 'key' => 'serviceId', 'allowed' => \App\Modules\Workflow\Configuration\Permissions::allows('activities')],
  'technical_reports' => ['component' => 'service-related-reports', 'key' => 'serviceId', 'allowed' => \App\Modules\Workflow\Configuration\Permissions::allows('technical_reports')],
 ],
 'editorDraftMessage' => 'Completa el ticket y los datos del servicio para guardar y consultar sus registros relacionados.',
 'editorHint' => 'Selecciona un ticket. El equipo y el técnico se obtienen de esa relación; el código se genera al guardar.',
])
