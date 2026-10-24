@if(!$report)<p class="record-editor-hint">Completa General y Plantilla para crear la primera revisión.</p>
@else
 @php($snapshot = $report->snapshot)
 @php($data = $snapshot['data'] ?? [])
 <section class="record-editor-section report-review">
  <h3>Datos capturados · R{{ $report->revision }} · Borrador</h3>
  <p class="record-editor-hint">Esta revisión conserva los datos guardados. Los cambios posteriores en el servicio no se incorporan hasta actualizarla.</p>
  <dl class="report-context">
   @foreach(['Informe' => data_get($data,'report.title'), 'Servicio' => data_get($data,'service.number'), 'Tipo' => data_get($data,'service.type'), 'Fecha del servicio' => data_get($data,'service.date'), 'Ticket' => data_get($data,'ticket.number'), 'Institución' => data_get($data,'institution.name'), 'Cliente' => data_get($data,'client.name'), 'Equipo' => data_get($data,'equipment.name'), 'Fabricante' => data_get($data,'equipment.manufacturer'), 'Marca' => data_get($data,'equipment.brand'), 'Modelo' => data_get($data,'equipment.model'), 'Serie' => data_get($data,'equipment.serial'), 'Técnico' => data_get($data,'technician.name')] as $label => $value)
    <div><dt>{{ $label }}</dt><dd>{{ $value ?: 'Sin registrar' }}</dd></div>
   @endforeach
   <div><dt>Diseño capturado</dt><dd>{{ data_get($snapshot,'metadata.template_version') ? 'Versión publicada '.data_get($snapshot,'metadata.template_version') : 'Diseño en borrador' }}</dd></div>
   <div><dt>Capturado el</dt><dd>{{ data_get($snapshot,'metadata.captured_at') ? \Carbon\Carbon::parse(data_get($snapshot,'metadata.captured_at'))->format('d/m/Y H:i') : 'Sin registrar' }}</dd></div>
  </dl>
  <details open><summary>Resumen del servicio</summary><p>{{ data_get($data,'service.summary') ?: 'Sin registrar' }}</p></details>
  <details open><summary>Actividades y evidencias ({{ count($data['activities'] ?? []) }})</summary>
   @forelse($data['activities'] ?? [] as $activity)
    <article class="report-activity"><strong>{{ $activity['description'] }}</strong><p>{{ $activity['result'] ?: 'Resultado pendiente' }}</p><small>{{ count($activity['photos'] ?? []) }} fotografía(s) incluidas en el PDF</small></article>
   @empty<p>No hay actividades registradas.</p>@endforelse
  </details>
  <details><summary>Recomendaciones</summary>@forelse($data['recommendations'] ?? [] as $text)<p>{{ $text }}</p>@empty<p>Sin registrar</p>@endforelse</details>
  <p class="record-editor-hint">Repuestos, componentes y firmas se incluirán cuando estén vinculados a datos de esos módulos.</p>
  @if($canManage)<button type="button" class="ops-button" wire:click="refreshReportData({{ $editingId }}, '{{ $editorSession }}')" wire:loading.attr="disabled">Actualizar datos · Nueva revisión</button>@endif
  @if($pdfNotice)<p role="status">{{ $pdfNotice }}</p>@endif
 </section>
@endif
