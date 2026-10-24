@php($isRelation = in_array($definition['type'], ['equipment', 'technicians']))
@php($disabled = !$canManage || ($isRelation && $relationsFrozen) || ($field === 'equipment_id' && $origin === 'scheduled'))
<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="ticket-field-{{ $field }}">
 @php($inputId = 'ticket-field-'.$field)
 <label for="{{ $inputId }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>
 @if($definition['type'] === 'origin')
  <select id="{{ $inputId }}" wire:model="origin" @disabled(!$canManage || $origin === 'scheduled')>
   @foreach(\App\Modules\Tickets\Configuration\Origins::all() as $id => $label)
    @if(($id !== 'scheduled' && $id !== 'legacy') || $origin === $id)<option value="{{ $id }}">{{ $label }}</option>@endif
   @endforeach
  </select>
 @elseif($definition['type'] === 'requester')
  <select id="{{ $inputId }}" wire:model="requester_contact_id" @disabled(!$canManage || !$canViewContacts)><option value="">Sin solicitante vinculado</option>
   @foreach($requesterOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
  </select><small class="record-editor-hint">Opcional. Selecciona primero el equipo; puedes registrar quién lo reportó como texto.</small>
 @elseif($definition['type'] === 'equipment')
  @if($canManage && !$relationsFrozen)<input type="search" @input.stop @change.stop aria-label="Buscar equipo del ticket" placeholder="Buscar nombre, código o serie…" wire:model.live.debounce.350ms="equipmentSearch">@endif
  <select id="{{ $inputId }}" wire:model="equipment_id" required @disabled($disabled)><option value="">Seleccionar...</option>
   @foreach($equipmentOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
  </select>
  @if($relationsFrozen)<small class="record-editor-hint">El ticket tiene servicios: se conservan el equipo y el técnico asignados.</small>@endif
 @elseif($definition['type'] === 'technicians')
  <select id="{{ $inputId }}" wire:model="technician_id" required @disabled($disabled)><option value="">Seleccionar...</option>
   @foreach($technicianOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
  </select>
 @elseif(in_array($definition['type'], ['status','priority']))
  @php($options = $definition['type'] === 'status' ? \App\Modules\Tickets\Configuration\Fields::statuses() : \App\Modules\Tickets\Configuration\Fields::priorities())
  <select id="{{ $inputId }}" wire:model="{{ $field }}" required @disabled($disabled)>@foreach(array_filter($options, fn($id) => !in_array($id,['unknown','historical']) || $this->$field === $id, ARRAY_FILTER_USE_KEY) as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select>
 @elseif($definition['type'] === 'textarea')
  <textarea id="{{ $inputId }}" wire:model="{{ $field }}" rows="4" maxlength="{{ $definition['max'] }}" @disabled($disabled)></textarea>
 @else
  <input id="{{ $inputId }}" type="{{ $definition['type'] }}" wire:model="{{ $field }}" @required($definition['required'] ?? false) @disabled($disabled)
   @if(isset($definition['max'])) maxlength="{{ $definition['max'] }}" @endif>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
