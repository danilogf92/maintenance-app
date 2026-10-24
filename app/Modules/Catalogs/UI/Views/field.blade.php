<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="catalog-field-{{ $field }}">
 @php($inputId = 'catalog-field-'.$field)
 <label for="{{ $inputId }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>
 @if($definition['type'] === 'checkbox')
  <label class="record-editor-check"><input id="{{ $inputId }}" type="checkbox" wire:model="active" @disabled(!$canManage)>Disponible para nuevas selecciones</label>
 @elseif(in_array($definition['type'], ['manufacturers', 'brands']))
  @if(\App\Modules\Workflow\Configuration\Permissions::allows($definition['type']))
   @if($canManage)<input type="search" @input.stop @change.stop aria-label="Buscar registro relacionado" placeholder="Buscar…" wire:model.live.debounce.350ms="parentSearch">@endif
   <select id="{{ $inputId }}" wire:model="{{ $field }}" @disabled(!$canManage)><option value="">Sin asignar</option>
    @foreach($parentOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
   </select>
  @else<p class="record-editor-hint">La relación se conserva según tus permisos.</p>@endif
  @if($field === 'manufacturer_id')<small class="record-editor-hint">El fabricante proporciona el logo del informe. Completa esta relación cuando esté verificada.</small>@endif
 @elseif($definition['type'] === 'textarea')
  <textarea id="{{ $inputId }}" wire:model="{{ $field }}" maxlength="{{ $definition['max'] }}" rows="4" @disabled(!$canManage)></textarea>
 @elseif($definition['type'] === 'number')
  <input id="{{ $inputId }}" type="number" min="0" max="{{ $definition['max'] }}" step="1" wire:model="{{ $field }}" @disabled(!$canManage)>
  <small class="record-editor-hint">Es una recomendación del modelo. No crea ni modifica planes automáticamente.</small>
 @else
  <input id="{{ $inputId }}" type="{{ $definition['type'] }}" wire:model="{{ $field }}" maxlength="{{ $definition['max'] }}" @required($definition['required'] ?? false) @disabled(!$canManage)>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
