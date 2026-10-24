@if(!empty($protocolContext))
 @php($snapshot = $protocolContext['snapshot'])
 <section class="record-editor-section">
  <h3>{{ $snapshot['protocol_name'] ?? '' }}</h3>
  <p>{{ $snapshot['protocol_code'] ?? '' }} · {{ $snapshot['version'] ?: 'Sin versión recibida' }} · Paso {{ $snapshot['step_number'] ?? $snapshot['position'] ?? '' }}</p>
  <p>Tipo: {{ $snapshot['type'] ?: 'Sin dato' }} · Rango: {{ $snapshot['range'] ?: 'Sin dato' }}</p>
  <p class="record-editor-hint">{{ $snapshot['include_in_report'] ? 'Actividad incluida en el informe.' : 'Instrucción interna, excluida del informe.' }}</p>
  <h4>Instrucciones de la versión aplicada</h4>
  <div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $snapshot['instruction'] ?: 'Sin instrucciones recibidas.' }}</div>
  <h4 style="margin-top:12px">Texto recibido para el informe</h4>
  <div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $snapshot['report_text'] ?: 'Sin texto recibido.' }}</div>
  <h4 style="margin-top:12px">Archivos de referencia</h4>
  <p class="record-editor-hint">Las evidencias del trabajo se registran por separado en Evidencias.</p>
  @foreach($protocolContext['files'] as $file)
   <p><a href="{{ route('operations.protocol-file', $file) }}" target="_blank" rel="noopener">{{ $file->name }} ↗</a></p>
  @endforeach
 </section>
@else<p class="record-editor-hint">Esta actividad no tiene un protocolo aplicado disponible para consultar.</p>@endif
