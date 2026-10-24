<label class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="record-editor-field-{{ $field }}">
 <span>{{ $definition['label'] }}{{ $definition['required'] ? ' *' : '' }}</span>
 @if($definition['type'] === 'select')
 <select wire:model="{{ $field }}" @required($definition['required'])>
  @foreach($definition['options'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
 </select>
 @else
 <input type="{{ $definition['type'] }}" wire:model="{{ $field }}" @required($definition['required'])
  @if(isset($definition['max'])) maxlength="{{ $definition['max'] }}" @endif>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</label>
