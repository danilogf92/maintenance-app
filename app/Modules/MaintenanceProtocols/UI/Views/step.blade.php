<details class="record-editor-section" @if($first ?? false) open @endif>
 <summary style="cursor:pointer;font-weight:600">Paso {{ $step->step_number ?? $step->position ?? 'sin número' }} · {{ $step->type ?: 'Sin tipo recibido' }}</summary>
 @if(!$step->maintenance_protocol_id)<p class="record-editor-hint">Protocolo de origen: {{ $step->parent_source_id ?: 'sin referencia' }} · Paso de origen: {{ $step->source_id }}</p>@endif
 <p class="record-editor-hint">{{ $step->include_in_report ? 'Se incluye en el informe' : 'Instrucción interna; no se incluye en el informe' }}</p>
 @if($step->range)<p><strong>Rango / valor de referencia:</strong> {{ $step->range }}</p>@endif
 <h4>Instrucciones</h4>
 <div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $step->instruction ?: 'Sin instrucciones recibidas.' }}</div>
 <h4 style="margin-top:12px">Texto sugerido para el informe</h4>
 <div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $step->report_text ?: 'Sin texto recibido.' }}</div>
 @if($step->files->isNotEmpty())
 <h4 style="margin-top:12px">Referencias del procedimiento</h4>
 <p class="record-editor-hint">Estos archivos son una guía; no son evidencias del servicio.</p>
 <div style="display:flex;flex-wrap:wrap;gap:12px">
 @foreach($step->files as $file)
  <a href="{{ route('operations.protocol-file', $file) }}" target="_blank" rel="noopener" title="{{ $file->name }}">
   @if(str_starts_with($file->mime, 'image/'))<img src="{{ route('operations.protocol-file', $file) }}" alt="{{ $file->name }}" loading="lazy" style="max-width:220px;max-height:160px;object-fit:contain;border:1px solid #CBD5E1;border-radius:8px">
   @else<x-ui.icon name="file-text" />{{ $file->name }} · Abrir PDF ↗@endif
  </a>
 @endforeach
 </div>
 @endif
</details>
