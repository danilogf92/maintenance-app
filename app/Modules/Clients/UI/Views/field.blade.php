<label class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="client-field-{{ $field }}">
 @if($definition['type'] === 'checkbox')
  <span class="record-editor-check"><input type="checkbox" wire:model="{{ $field }}" @disabled(!$canManage)>{{ $definition['label'] }}</span>
 @else
  <span>{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</span>
  <input type="{{ $definition['type'] }}" wire:model="{{ $field }}" @required($definition['required'] ?? false)
   maxlength="{{ $definition['max'] }}" @disabled(!$canManage)>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</label>
