@if(!$ninoxDetails)
 <p class="record-editor-hint">Este ticket no tiene información adicional del archivo recibido.</p>
@else
 @php($source = $ninoxDetails['source'])
 <p role="status">Ticket histórico importado, disponible en modo consulta.</p>
 <section class="record-editor-section">
  <h3>Datos del ticket · Referencia {{ $source['ninox_id'] }}</h3>
  <x-record-editor.source-details>
   @foreach(['ticket_code'=>'Código original','ticket_date'=>'Fecha original (sin hora)','status'=>'Estado original',
    'report_client'=>'Motivo / reporte del cliente','technical_info'=>'Información para el técnico','service_type'=>'Tipo de servicio',
    'service_type_2'=>'Tipo de servicio (campo anterior)','value_12'=>'Centro registrado','observations'=>'Observaciones',
    'is_email_sent'=>'Correo enviado','value_22'=>'Motivo de última visita'] as $key=>$label)
    <div><dt>{{ $label }}</dt><dd style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $source[$key] ?? '—' }}</dd></div>
   @endforeach
  </x-record-editor.source-details>
 </section>
 <section class="record-editor-section">
  <h3>Relaciones de origen</h3>
  <p>Equipo de referencia: {{ $source['value_16'] ?? 'Sin referencia' }} · {{ $ninoxDetails['equipment_resolved'] ? 'Vinculado' : 'Pendiente' }}</p>
  @foreach($ninoxDetails['engineers'] as $engineer)<p>Ingeniero de servicio: <a href="{{ route('operations.field_engineers',['record'=>$engineer->id]) }}">{{ $engineer->name }}</a> · Asignación de referencia {{ $engineer->pivot->source_assignment_id }}</p>@endforeach
  <p>Asignaciones FSE: {{ implode(', ',$source['field_service_engineer'] ?? []) ?: 'Sin referencias' }}</p>
  <p class="record-editor-hint">Las asignaciones FSE no se convierten en IDs de usuario. Los vínculos de ingenieros se muestran arriba cuando están verificados. Las cuentas de acceso se gestionan por separado.</p>
  <p>Contacto en sitio: {{ $source['contact_on_site'] ?? 'Sin referencia' }} · Pendiente de vincular.</p>
  <p class="record-editor-hint">Contacto en sitio no se asigna automáticamente como solicitante. No se ha atribuido quién reportó el ticket.</p>
  <details><summary>Cronogramas de contrato · {{ count($source['value_6'] ?? []) }} referencias</summary>
   <p>{{ implode(', ',$source['value_6'] ?? []) ?: 'Sin referencias' }}</p>
   <p>Fecha por contrato: {{ $source['value_9'] ?? 'Sin fecha' }}. Pendiente de importar Contratos/Cronogramas; no crea planificación.</p>
  </details>
 </section>
 <section class="record-editor-section">
  <h3>Bitácoras vinculadas · {{ $ninoxDetails['services']->count() }}</h3>
  @forelse($ninoxDetails['services'] as $service)
   <p><a class="ops-button" href="{{ route('operations.services',['record'=>$service->id]) }}">{{ $service->code }}</a></p>
  @empty<p>No hay bitácoras vinculadas visibles para tu usuario.</p>@endforelse
  <details style="margin-top:12px"><summary>Referencias de bitácoras · {{ count($source['value_15'] ?? []) }}</summary>
   <p style="overflow-wrap:anywhere">{{ implode(', ',$source['value_15'] ?? []) ?: 'Sin referencias' }}</p>
  </details>
 </section>
@endif
