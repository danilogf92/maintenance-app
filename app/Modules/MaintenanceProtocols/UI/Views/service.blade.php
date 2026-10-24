<div>
 <h3 class="text-base font-semibold">Protocolos del servicio</h3>
 <p class="record-editor-hint">Revisa el procedimiento adecuado antes de aplicarlo. La marca y el modelo recibidos son informativos; no se selecciona automáticamente.</p>
 @if($feedback)<p role="status" class="ops-notice">{{ $feedback }}</p>@endif
 @error('protocolId')<p role="alert" class="ops-error">{{ $message }}</p>@enderror
 @if($canApply)
 <div class="record-editor-section" style="display:grid;gap:12px">
  <label>Buscar protocolo<input class="record-related-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Nombre o código"></label>
  <label>Protocolo a aplicar<select class="record-related-search" wire:model.live="protocolId"><option value="">Selecciona un protocolo</option>
   @foreach($options as $option)<option value="{{ $option->id }}">{{ $option->name ?: 'Sin nombre recibido' }} · {{ $option->code }} · {{ $option->steps_count }} pasos · {{ $option->model_info }}</option>@endforeach
  </select></label>
  @if($preview)
   <p>{{ $preview->name }} · {{ $preview->version ?: 'Sin versión recibida' }} · {{ $preview->steps->count() }} actividades por crear.</p>
   <a href="{{ route('operations.maintenance_protocols', ['record' => $preview->id]) }}" target="_blank" rel="noopener">Abrir protocolo completo ↗</a>
   <details class="record-editor-section"><summary style="cursor:pointer">Revisar pasos antes de aplicar</summary>
    @foreach($preview->steps as $step)
     @include('maintenance-protocols::step')
    @endforeach
   </details>
   <button class="ops-btn" type="button" wire:click="apply" wire:loading.attr="disabled" wire:target="apply">Aplicar protocolo al servicio</button>
   <span wire:loading wire:target="apply" role="status">Creando actividades…</span>
   <p class="record-editor-hint">Se crean actividades sin resultado ni fotos de evidencia. Repetir la aplicación conserva las mismas actividades.</p>
  @endif
 </div>
 @else<p class="record-editor-hint">Aplicación disponible en servicios abiertos y editables, con permisos para modificar servicios y actividades.</p>@endif
 <h4 style="margin-top:16px;font-weight:600">Procedimientos aplicados</h4>
 @forelse($applications as $application)
 <details class="record-editor-section" wire:key="protocol-application-{{ $application->id }}">
  <summary style="cursor:pointer">{{ $application->snapshot['name'] ?? '' }} · {{ $application->snapshot['version'] ?? 'Sin versión' }} · {{ $application->activities_count }} actividades</summary>
  <p>{{ $application->results_count }} con resultado registrado. Aplicado: {{ $application->created_at->format('d/m/Y H:i') }}.</p>
  <p class="record-editor-hint">La aplicación conserva la versión e instrucciones utilizadas. Aplicar el protocolo no completa el servicio.</p>
  @if(\App\Modules\Workflow\Configuration\Permissions::allows('activities'))
  <ul style="display:grid;gap:8px;margin:12px 0">
   @foreach($application->activities as $activity)
    <li><a href="{{ route('operations.activities', ['record' => $activity->id]) }}">{{ $activity->code }} · {{ $activity->description }}</a>
    <span class="record-editor-hint">{{ trim($activity->result ?? '') !== '' ? 'Resultado registrado' : 'Resultado pendiente' }}</span></li>
   @endforeach
  </ul>
  @endif
  <a href="{{ route('operations.maintenance_protocols', ['record' => $application->maintenance_protocol_id]) }}" target="_blank" rel="noopener">Consultar catálogo ↗</a>
 </details>
 @empty<p>No se ha aplicado ningún protocolo.</p>@endforelse
</div>
