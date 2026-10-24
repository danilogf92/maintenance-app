@php($isRelation = in_array($definition['type'], ['clients', 'institutions']))
@php($disabled = !$canManage || ($isRelation && $relationsFrozen))
<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="equipment-field-{{ $field }}">
 @php($inputId = 'equipment-field-'.$field)
 <label for="{{ $inputId }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>
 @if($isRelation)
  @if($canManage && !$relationsFrozen)
   <input type="search" @input.stop @change.stop aria-label="{{ $field === 'client_id' ? 'Buscar cliente propietario' : 'Buscar institución' }}"
    placeholder="Buscar…" wire:model.live.debounce.350ms="{{ $field === 'client_id' ? 'clientSearch' : 'institutionSearch' }}">
  @endif
  <select id="{{ $inputId }}" wire:model="{{ $field }}" required @disabled($disabled)>
   <option value="">Seleccionar...</option>
   @foreach($field === 'client_id' ? $clientOptions : $institutionOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
  </select>
  @if($relationsFrozen && $field === 'client_id')<small class="record-editor-hint">El equipo tiene historial: cliente e institución se conservan. Las transferencias se gestionarán en una etapa posterior.</small>@endif
 @elseif(in_array($definition['type'], ['brands', 'equipment_models']))
  @if(\App\Modules\Workflow\Configuration\Permissions::allows($definition['type']))
   <select id="{{ $inputId }}" wire:model.live="{{ $field }}" @disabled(!$canManage)><option value="">Sin asignar</option>
   @foreach($field === 'brand_id' ? $brandOptions : $modelOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
   </select>
  @else<p class="record-editor-hint">La relación se conserva según tus permisos.</p>@endif
  @if($field === 'brand_id')
   @if(\App\Modules\Workflow\Configuration\Permissions::allows('manufacturers'))<small class="record-editor-hint">Fabricante: {{ $manufacturerName ?: 'Pendiente de asignar en Marcas' }}</small>@endif
   @if($canManage)<input type="search" @input.stop @change.stop aria-label="Buscar marca o modelo" placeholder="Buscar en los catálogos…" wire:model.live.debounce.350ms="catalogSearch">@endif
  @endif
 @elseif($definition['type'] === 'status')
  <select id="{{ $inputId }}" wire:model="status" required @disabled($disabled)>
   @foreach(\App\Modules\Equipment\Configuration\Fields::statuses() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
  </select>
 @else
  <input id="{{ $inputId }}" type="text" wire:model="{{ $field }}" maxlength="{{ $definition['max'] }}" @required($definition['required'] ?? false) @disabled($disabled)>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
