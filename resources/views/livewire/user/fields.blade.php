 <label>Nombre *<input wire:model="name" required>@error('name')<small>{{ $message }}</small>@enderror</label>
 <label>Correo electrónico *<input type="email" wire:model="email" required>@error('email')<small>{{ $message }}</small>@enderror</label>
 <label>Institución *<select wire:model="institution_id" required><option value="">Seleccionar...</option>@foreach($institutions as $institution)<option value="{{ $institution->id }}">{{ $institution->name }}</option>@endforeach</select>@error('institution_id')<small>{{ $message }}</small>@enderror</label>
 <label>Cargo<input wire:model="job_title">@error('job_title')<small>{{ $message }}</small>@enderror</label>
 <label>Identificación<input wire:model="identification">@error('identification')<small>{{ $message }}</small>@enderror</label>
 <label>Teléfono<input wire:model="phone">@error('phone')<small>{{ $message }}</small>@enderror</label>
 <label>{{ $editingId ? 'Nueva contraseña (dejar vacío para conservar)' : 'Contraseña inicial (mínimo 12 caracteres) *' }}<input type="password" wire:model="password" autocomplete="new-password" {{ $editingId ? '' : 'required' }}>@error('password')<small>{{ $message }}</small>@enderror</label>
 <div class="ops-role-box w-full"><span>Roles</span>@foreach($roles as $role)@if(auth()->user()->hasRole('SuperAdmin') || !in_array($role->name, ['SuperAdmin']))<label class="ops-check"><input type="checkbox" wire:model="role_ids" value="{{ $role->id }}">{{ $role->name }}</label>@endif @endforeach @error('role_ids')<small>{{ $message }}</small>@enderror</div>
 <label class="ops-check"><input type="checkbox" wire:model="is_active">Cuenta activa</label>
 @if(auth()->user()->hasRole('SuperAdmin'))<label class="ops-check"><input type="checkbox" wire:model="can_access_admin">Permitir acceso administrativo (requiere permiso)</label>@endif

