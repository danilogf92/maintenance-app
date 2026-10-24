<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}">
 <label for="contract-field-{{ $field }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>
 @if($definition['type']==='client')<select id="contract-field-{{ $field }}" wire:model="client_id" @disabled(!$canManage)><option value="">Sin cliente vinculado</option>@foreach($clientOptions as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
 @elseif($definition['type']==='checkbox')<input id="contract-field-{{ $field }}" type="checkbox" wire:model="{{ $field }}" @disabled(!$canManage)>
 @elseif($definition['type']==='textarea')<textarea id="contract-field-{{ $field }}" wire:model="{{ $field }}" rows="4" maxlength="{{ $definition['max'] }}" @disabled(!$canManage)></textarea>
 @else<input id="contract-field-{{ $field }}" type="text" wire:model="{{ $field }}" maxlength="{{ $definition['max'] }}" @disabled(!$canManage)>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
