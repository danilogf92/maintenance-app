@if(!$ninoxDetails)
 <p class="record-editor-hint">Este servicio se creó en la aplicación; no tiene información adicional del archivo recibido.</p>
@else
 @php($origin = $ninoxDetails['data'])
 <p role="status" class="record-editor-hint">Bitácora histórica importada. Sus datos originales se conservan en modo de consulta.</p>
 <section class="record-editor-section">
  <h3>Datos del servicio · Referencia {{ $origin['ninox_id'] }}</h3>
  <x-record-editor.source-details>
   @foreach(['report_code' => 'Número de bitácora', 'client_name' => 'Cliente', 'institution_name' => 'Institución', 'city' => 'Ciudad',
    'equipment_location' => 'Ubicación', 'engineer_assigned' => 'Técnico registrado', 'service_type_requested' => 'Servicio solicitado',
    'service_type_realized' => 'Servicio realizado', 'final_system_status' => 'Estado final del equipo', 'next_step' => 'Siguiente paso',
    'report_type' => 'Tipo de reporte', 'is_send_report' => 'Enviado', 'visit_reason' => 'Motivo de visita', 'value_21' => 'Firmó'] as $key => $label)
   <div><dt>{{ $label }}</dt><dd style="overflow-wrap:anywhere">{{ $origin[$key] ?? '—' }}</dd></div>
   @endforeach
  </x-record-editor.source-details>
 </section>
 <section class="record-editor-section">
  <h3>Relaciones</h3>
  <p>Ticket de referencia: {{ $origin['tickets_associated'] ?? 'Sin ticket de origen' }} · {{ $ninoxDetails['ticket_resolved'] ? 'Vinculado' : 'Pendiente de equivalencia' }}</p>
  @if($ninoxDetails['ticket'])<p><a class="ops-button" href="{{ route('operations.tickets', ['record'=>$ninoxDetails['ticket']->id]) }}">Abrir ticket {{ $ninoxDetails['ticket']->code }}</a></p>@endif
  @if($ninoxDetails['engineer'])<p>Ingeniero de servicio verificado: <a href="{{ route('operations.field_engineers',['record'=>$ninoxDetails['engineer']->id]) }}">{{ $ninoxDetails['engineer']->name }}</a></p>@endif
  <p>Técnico: {{ $ninoxDetails['technician_resolved'] ? 'Cuenta vinculada' : 'Cuenta de acceso sin vincular' }}</p>
  @foreach($ninoxDetails['links'] as $label => $ids)
   <details style="margin-top:12px"><summary>{{ $label }} · {{ count($ids) }} referencias de origen</summary>
    <p class="record-editor-hint">Pendientes de importar y vincular con su tabla de origen.</p>
    <p style="overflow-wrap:anywhere">{{ implode(', ', $ids) ?: 'Sin referencias' }}</p>
   </details>
  @endforeach
 </section>
 <section class="record-editor-section">
  <h3>Archivos históricos</h3>
  @forelse($ninoxDetails['documents'] as $document)
   <div style="margin-top:12px;overflow-wrap:anywhere">
    <strong>{{ $document->kind === 'report_pdf' ? 'PDF' : 'Firma del técnico' }}</strong> · {{ $document->name }}
    @if($document->status === 'ready')
     <a class="ops-button" target="_blank" rel="noopener" href="{{ route('services.document', [$editingId, $document->id]) }}">Abrir archivo</a>
    @else<p role="status">{{ $document->status === 'invalid' ? 'Archivo rechazado: no coincide con el original o su formato.' : 'Archivo pendiente: no se encontró en las carpetas disponibles.' }}</p>@endif
   </div>
  @empty<p>No hay archivos referenciados en esta exportación.</p>@endforelse
 </section>
@endif
