<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="activity-field-{{ $field }}">
 @php($inputId = 'activity-field-'.$field)
 @if($field !== 'photos')<label for="{{ $inputId }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>@endif
 @if($field === 'service_id')
  @if($canManage)<input type="search" @input.stop @change.stop aria-label="Buscar servicio de la actividad" placeholder="Buscar código o tipo…" wire:model.live.debounce.350ms="serviceSearch">@endif
  <select id="{{ $inputId }}" wire:model="service_id" required @disabled(!$canManage)><option value="">Seleccionar...</option>
   @foreach($serviceOptions as $id => $label)<option value="{{ $id }}" @selected((string)$service_id === (string)$id)>{{ $label }}</option>@endforeach
  </select>
  @if($activityContext)
   <dl class="activity-context">
    <div><dt>Técnico del servicio</dt><dd>{{ $activityContext['technician']?->name ?: 'Sin técnico disponible' }}</dd></div>
    @if($activityContext['ticket'])<div><dt>Ticket</dt><dd>{{ $activityContext['ticket']->code }}</dd></div>@endif
    @if($activityContext['equipment'])<div><dt>Equipo relacionado</dt><dd>{{ $activityContext['equipment']->code }} · {{ $activityContext['equipment']->name }}</dd></div>@endif
   </dl>
  @else<small class="record-editor-hint">El contexto del servicio se muestra según tus permisos.</small>@endif
 @elseif($field === 'photos')
  @include('activities::evidence')
 @else
  <textarea id="{{ $inputId }}" wire:model="{{ $field }}" rows="{{ $field === 'description' ? 6 : 5 }}" maxlength="{{ $definition['max'] }}" @required($definition['required'] ?? false) @disabled(!$canManage)></textarea>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
