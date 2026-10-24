@if(!$ninoxOrigin)
 <p class="record-editor-hint">Este registro no tiene información adicional del archivo recibido.</p>
@else
 @php($original = $ninoxOrigin->payload)
 <section class="record-editor-section">
  <h3>Datos del registro · Referencia {{ $ninoxOrigin->ninox_id }}</h3>
  <p>Nombre original: {{ $original[$catalog === 'brands' ? 'brand_name' : 'model_name'] }}</p>
  <p class="record-editor-hint">Los datos originales se conservan. Editar la ficha actual no cambia esta copia de origen.</p>
  @if($catalog === 'brands')
   <p>Proveedor registrado: {{ $original['supplier'] ?? 'Sin referencia' }} · Pendiente de importar Proveedores.</p>
   <p class="record-editor-hint">Casa comercial y proveedor no asignan automáticamente un fabricante.</p>
  @else
   <p role="status">{{ $ninoxOrigin->brand_basis === 'equipment_reference' ? 'Marca confirmada por las referencias directas de los equipos recibidos.' : ($ninoxOrigin->source_brand_id ? 'Vínculo inicial con marca de referencia '.$ninoxOrigin->source_brand_id.' deducido por los equipos comunes de ambas exportaciones. Confirmar cuando se importe Equipos.' : 'Marca pendiente: este modelo no tiene equipos comunes suficientes para determinarla.') }}</p>
   <h3 style="margin-top:16px">Valores de mantenimiento originales</h3>
   @foreach(['time_preventive_maintenance' => 'Preventivo', 'time_overhaul' => 'Overhaul', 'time_corrective_maintenance' => 'Correctivo'] as $key => $label)
    <p>{{ $label }}: {{ $original[$key] ?? 'Sin dato' }}</p>
   @endforeach
   <p class="record-editor-hint">La exportación no identifica la unidad de estos tiempos. No se convierten a días ni meses ni generan planes.</p>
   <details><summary>Documentación relacionada · {{ count($original['documentation_equipment'] ?? []) }} referencias</summary>
    <p style="overflow-wrap:anywhere">{{ implode(', ', $original['documentation_equipment'] ?? []) ?: 'Sin referencias' }}</p>
    <p>Pendiente de recibir la tabla Documentación de Equipo.</p>
   </details>
  @endif
  <details style="margin-top:16px"><summary>Equipos de origen · {{ count($original[$catalog === 'brands' ? 'equipment_id' : 'equipments'] ?? []) }}</summary>
   <p style="overflow-wrap:anywhere">{{ implode(', ', $original[$catalog === 'brands' ? 'equipment_id' : 'equipments'] ?? []) ?: 'Sin equipos asociados' }}</p>
   <p class="record-editor-hint">Son referencias originales, pendientes de vincular a los equipos de esta plataforma.</p>
  </details>
 </section>
 <section class="record-editor-section"><h3>{{ $catalog === 'brands' ? 'Logo de la marca' : 'Imagen del modelo' }}</h3>
  @forelse($ninoxOrigin->assets as $asset)
   @if($asset->status === 'ready')
    <a href="{{ route('catalogs.asset', [$catalog, $editingId, $asset->id]) }}" target="_blank" rel="noopener">
     <img src="{{ route('catalogs.asset', [$catalog, $editingId, $asset->id]) }}" alt="{{ $asset->name }}" style="max-width:100%;max-height:200px;object-fit:contain;margin:12px 0;background:white;padding:8px;border-radius:8px">
     <span>Abrir imagen original</span>
    </a>
   @else<p role="status">Imagen {{ $asset->status === 'missing' ? 'pendiente' : 'rechazada' }}: {{ $asset->name }}</p>@endif
  @empty<p>Sin imagen de origen.</p>@endforelse
 </section>
@endif
