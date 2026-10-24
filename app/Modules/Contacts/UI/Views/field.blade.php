<label class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="contact-field-{{ $field }}">
 @if($definition['type'] === 'checkbox')
  <span class="record-editor-check"><input type="checkbox" wire:model="{{ $field }}" @disabled(!$canManage)>{{ $definition['label'] }}</span>
 @else
  <span>{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</span>
  @if($definition['type'] === 'textarea')<textarea wire:model="{{ $field }}" maxlength="{{ $definition['max'] }}" @disabled(!$canManage)></textarea>
  @else<input type="{{ $definition['type'] }}" wire:model="{{ $field }}" @required($definition['required'] ?? false)
   maxlength="{{ $definition['max'] }}" @disabled(!$canManage)>@endif
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</label>
