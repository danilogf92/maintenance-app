@if(!$receivedDetails)
<p class="record-editor-hint">Este equipo se creó en la aplicación; no tiene datos de un archivo recibido.</p>
@else
@php($source = $receivedDetails['source'])
@php($original = $receivedDetails['equipment'])
<p role="status" class="record-editor-hint">Datos recibidos en consulta. El estado operativo permanece por verificar. Consulta abajo las asignaciones actuales y sus historiales.</p>
<p class="record-editor-hint">Referencia {{ $source['ninox_id'] }}. El nombre de pantalla usa el componente principal o el modelo registrado; la serie identifica cada fila.</p>
<section class="record-editor-section"><h3>Relaciones verificadas</h3>
<p>Marca de referencia {{ $source['brand_id'] ?? '—' }} · {{ $original->brand_id ? 'Vinculada' : 'Pendiente' }}</p>
<p>Modelo de referencia {{ $source['model_id'] ?? '—' }} · {{ $original->equipment_model_id ? 'Vinculado' : 'Falta en el catálogo recibido' }}</p>
<p>Cliente: {{ $original->client_id ? 'Vinculado' : 'Pendiente de resolver' }} · Institución: {{ $original->institution_id ? 'Vinculada' : 'Pendiente de resolver' }}.</p>
<p>Las frecuencias diarias y los intervalos originales no crean planes mensuales automáticamente.</p></section>
@include('equipment::assignment-history')
@foreach(\App\Modules\Equipment\Configuration\ReceivedFields::groups() as $title=>$fields)
<details class="record-editor-section" @if($loop->first) open @endif><summary style="font-weight:700;cursor:pointer">{{ $title }}</summary>
<x-record-editor.source-details>
@foreach($fields as $key=>$label)<div><dt>{{ $label }}</dt><dd style="overflow-wrap:anywhere">{{ \App\Modules\Equipment\Configuration\ReceivedFields::display($source[$key] ?? null) }}</dd></div>@endforeach
</x-record-editor.source-details></details>
@endforeach
<p class="record-editor-hint">Esta exportación no contiene fotografías, firmas ni PDF de equipo. Los valores económicos y porcentajes conservan su escala original.</p>
@endif
