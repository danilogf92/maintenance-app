@if(empty($definition['super_admin_only']) || auth()->user()->hasRole('SuperAdmin'))
<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="user-field-{{ $field }}">
 @php($inputId = 'user-field-'.$this->getId().'-'.$field)
 @if($definition['type'] === 'roles')
  <fieldset class="record-editor-roles"><legend>{{ $definition['label'] }}</legend>
   @foreach($roles as $role)<label class="record-editor-check"><input type="checkbox" wire:model="role_ids" value="{{ $role->id }}">{{ $role->name }}</label>@endforeach
  </fieldset>
 @elseif($definition['type'] === 'checkbox')
  <label class="record-editor-check" for="{{ $inputId }}"><input id="{{ $inputId }}" type="checkbox" wire:model="{{ $field }}">{{ $definition['label'] }}</label>
  @if($field === 'can_access_admin')<small class="record-editor-hint">También requiere el permiso de acceso al panel administrativo.</small>@endif
 @else
  <label for="{{ $inputId }}">{{ $definition['type'] === 'password' ? ($editingId ? 'Nueva contraseña' : 'Contraseña inicial *') : $definition['label'].(!empty($definition['required']) ? ' *' : '') }}</label>
  @if($definition['type'] === 'institution')
   <select id="{{ $inputId }}" wire:model="{{ $field }}" required><option value="">Seleccionar...</option>@foreach($institutions as $institution)<option value="{{ $institution->id }}">{{ $institution->name }}</option>@endforeach</select>
  @else
   <input id="{{ $inputId }}" type="{{ $definition['type'] }}" wire:model="{{ $field }}"
    @if($definition['type'] === 'password') autocomplete="new-password" @required(!$editingId) aria-describedby="{{ $inputId }}-hint"
    @else maxlength="255" @required($definition['required'] ?? false) @endif>
   @if($definition['type'] === 'password')<small id="{{ $inputId }}-hint" class="record-editor-hint">{{ $editingId ? 'Deja vacío para conservar la contraseña actual.' : 'Mínimo 12 caracteres. La cuenta se crea al completar los campos obligatorios.' }}</small>@endif
  @endif
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
 @error($field.'.*')<small role="alert">{{ $message }}</small>@enderror
</div>
@endif
