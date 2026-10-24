<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="service-field-{{ $field }}">
 @php($inputId = 'service-field-'.$field)
 <label for="{{ $inputId }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>
 @if($field === 'ticket_id')
  @if($canManage && !$relationsFrozen)<input type="search" @input.stop @change.stop aria-label="Buscar ticket del servicio" placeholder="Buscar código o asunto…" wire:model.live.debounce.350ms="ticketSearch">@endif
  <select id="{{ $inputId }}" wire:model="ticket_id" required @disabled(!$canManage || $relationsFrozen)><option value="">Seleccionar...</option>
   @foreach($ticketOptions as $id => $label)<option value="{{ $id }}" @selected((string)$ticket_id === (string)$id)>{{ $label }}</option>@endforeach
  </select>
  @if($relationsFrozen)<small class="record-editor-hint">Este servicio tiene historial: se conserva el ticket relacionado.</small>@endif
  @if($serviceContext)
   <dl class="service-context">
    @if($serviceContext['equipment'])<div><dt>Equipo relacionado</dt><dd>{{ $serviceContext['equipment']->code }} · {{ $serviceContext['equipment']->name }}</dd></div>@endif
    <div><dt>Técnico del servicio</dt><dd>{{ $serviceContext['technician']?->name ?: 'Sin técnico disponible' }}</dd></div>
   </dl>
   <small class="record-editor-hint">El técnico se asigna desde el ticket y se conserva para el servicio guardado.</small>
  @else<small class="record-editor-hint">El contexto del ticket se muestra según tus permisos.</small>@endif
 @elseif($definition['type'] === 'status')
  <select id="{{ $inputId }}" wire:model="status" required @disabled(!$canManage)>@foreach(array_filter(\App\Modules\Services\Configuration\Fields::statuses(), fn($key) => $key !== 'historical' || $status === 'historical', ARRAY_FILTER_USE_KEY) as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select>
 @elseif($definition['type'] === 'textarea')
  <textarea id="{{ $inputId }}" wire:model="{{ $field }}" rows="{{ $field === 'summary' ? 6 : 4 }}" maxlength="{{ $definition['max'] }}" @disabled(!$canManage)></textarea>
  @if($field === 'recommendations')<small class="record-editor-hint">Escribe una recomendación por línea.</small>@endif
 @else
  <input id="{{ $inputId }}" type="{{ $definition['type'] }}" wire:model="{{ $field }}" @required($definition['required'] ?? false) @disabled(!$canManage) @if(isset($definition['max'])) maxlength="{{ $definition['max'] }}" @endif>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
