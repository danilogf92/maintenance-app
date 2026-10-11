<section class="ops-page flex h-full min-h-0 w-full flex-1 flex-col overflow-hidden" x-data="{showFilters:false}">
<x-operations.workspace-navigation :views="$operationViews" :active-id="$currentViewId">
    <x-slot:search><input type="search" placeholder="Buscar usuarios..." aria-label="Buscar usuarios..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:delete>
        <button type="button" class="ops-icon-button text-red-600 disabled:opacity-30" wire:click="requestDelete" @disabled(count($selectedIds) === 0) aria-label="Eliminar seleccionados" title="Eliminar seleccionados"><x-ui.icon name="trash" /></button>
    </x-slot:delete>
    <x-slot:recordNavigation>
        <button type="button" wire:click="navigateRecord('first')" title="Primer registro" aria-label="Primer registro"><x-ui.icon name="skip-back" /></button>
        <button type="button" wire:click="navigateRecord('previous')" title="Registro anterior" aria-label="Registro anterior"><x-ui.icon name="play-back" /></button>
        <button type="button" wire:click="navigateRecord('next')" title="Registro siguiente" aria-label="Registro siguiente"><x-ui.icon name="play-forward" /></button>
        <button type="button" wire:click="navigateRecord('last')" title="Último registro" aria-label="Último registro"><x-ui.icon name="skip-forward" /></button>
    </x-slot:recordNavigation>
    <x-slot:create><button type="button" class="ops-icon-button" wire:click="create" aria-label="Nuevo usuario" title="Nuevo usuario"><x-ui.icon name="plus" /></button></x-slot:create>
</x-operations.workspace-navigation>
@if($confirmDelete)
<div class="ops-delete-confirm" role="alertdialog" aria-modal="true" aria-label="Confirmar eliminación">
 <span>¿Eliminar {{ count($selectedIds) }} registro(s) seleccionados? Esta acción no se puede deshacer.</span>
 <div class="flex gap-2"><button type="button" wire:click="cancelDelete">Cancelar</button><button type="button" class="ops-delete-danger" wire:click="deleteSelected">Eliminar</button></div>
</div>
@endif
@if(session('error'))<div class="ops-error" role="alert">{{ session('error') }}</div>@endif
@if(session('success'))<div class="ops-success" role="status">{{ session('success') }}</div>@endif
<div class="ops-toolbar ops-filter-panel" x-show="showFilters" x-cloak><select aria-label="Filtrar institución" wire:model.live="institutionFilter"><option value="">Todas las instituciones</option>@foreach($institutions as $institution)<option value="{{ $institution->id }}">{{ $institution->name }}</option>@endforeach</select><select aria-label="Filtrar estado" wire:model.live="activeFilter"><option value="">Todos los estados</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
<div class="relative flex min-h-0 w-full flex-1 overflow-hidden" style="flex:1 1 0%;min-height:0;height:100%;" x-data="{ drawerWidth: 520, resizing: false, isMobile: false, storageKey: 'maintenance-app:operation-drawer-width',
 init() { this.isMobile = window.innerWidth < 768; try { const saved = Number(localStorage.getItem(this.storageKey)); if (Number.isFinite(saved) && saved >= 300) this.drawerWidth = saved; } catch(e) {} this.drawerWidth = this.clampWidth(this.drawerWidth); },
 clampWidth(value) { const available = this.$el?.clientWidth || window.innerWidth; const min = Math.min(320, available); const max = Math.max(min, available - Math.min(260, Math.floor(available * .25))); return Math.min(max, Math.max(min, Number(value) || 520)); },
 beginResize(event) { if (this.isMobile) return; this.resizing = true; event.currentTarget.setPointerCapture(event.pointerId); event.preventDefault(); document.body.style.cursor = 'col-resize'; document.body.style.userSelect = 'none'; },
 resizeTo(event) { if (!this.resizing) return; const rect = this.$el.getBoundingClientRect(); this.drawerWidth = this.clampWidth(rect.right - event.clientX); },
 finishResize() { if (!this.resizing) return; this.resizing = false; document.body.style.cursor = ''; document.body.style.userSelect = ''; try { localStorage.setItem(this.storageKey, String(Math.round(this.drawerWidth))); } catch(e) {} },
 syncWidth(event) { if (event.key !== this.storageKey || this.resizing) return; const value = Number(event.newValue); if (Number.isFinite(value) && value >= 300) this.drawerWidth = this.clampWidth(value); }
 }" @resize.window="isMobile = window.innerWidth < 768; drawerWidth = clampWidth(drawerWidth)" @pointermove.window="resizeTo($event)" @pointerup.window="finishResize()" @pointercancel.window="finishResize()" @storage.window="syncWidth($event)">
<div class="ops-table-wrap min-h-0 min-w-0 flex-1 overflow-auto" style="height:auto!important;min-height:0!important;flex:1 1 0%!important;" x-ref="records"><table class="ops-table"><thead><tr>
@foreach($visibleColumns as $column)<th wire:key="head-{{ $column }}">{{ $availableColumns[$column] ?? $column }}</th>@endforeach
</tr></thead><tbody>
@forelse($items as $item)
<tr wire:key="user-{{ $item->id }}" wire:click="edit({{ $item->id }})" class="cursor-pointer {{ $formOpen && $editingId === $item->id ? 'bg-blue-100 dark:bg-blue-900/40 ring-1 ring-inset ring-blue-400' : 'hover:bg-blue-50 dark:hover:bg-slate-800' }}" aria-selected="{{ $formOpen && $editingId === $item->id ? 'true' : 'false' }}" tabindex="0" @keydown.enter="$wire.edit({{ $item->id }})">
@foreach($visibleColumns as $column)
<td wire:key="cell-{{ $item->id }}-{{ $column }}" data-label="{{ $availableColumns[$column] ?? $column }}">
@switch($column)
@case('name') <strong>{{ $item->name }}</strong><div class="ops-muted">{{ $item->email }}</div> @break
@case('institution') {{ $item->institution?->name ?? '—' }} @break
@case('job_title') {{ $item->job_title ?: '—' }} @break
@case('roles') {{ $item->roles->pluck('name')->join(', ') ?: '—' }} @break
@case('status') <span class="ops-status">{{ $item->is_active ? 'Activo' : 'Inactivo' }}</span> @break
@case('id') {{ $item->id }} @break
@case('email') {{ $item->email }} @break
@case('institution_id') {{ $item->institution_id ?? '—' }} @break
@case('identification') {{ $item->identification ?: '—' }} @break
@case('phone') {{ $item->phone ?: '—' }} @break
@case('can_access_admin') {{ $item->can_access_admin ? 'Sí' : 'No' }} @break
@case('email_verified_at') {{ $item->email_verified_at?->format('d/m/Y H:i') ?? '—' }} @break
@case('theme_preference') {{ $item->theme_preference ?: 'Sistema' }} @break
@case('created_at') {{ $item->created_at?->format('d/m/Y H:i') ?? '—' }} @break
@case('updated_at') {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }} @break
@endswitch
</td>
@endforeach

</tr>
@empty<tr><td colspan="{{ count($visibleColumns) }}" class="ops-empty">No se encontraron registros.</td></tr>@endforelse
</tbody></table></div>

<aside x-show="$wire.formOpen" x-cloak
 class="relative flex h-full min-h-0 shrink-0 flex-col border-l border-slate-200 bg-slate-50 text-slate-900 shadow-xl dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
 :style="isMobile ? 'position:fixed;inset:0;z-index:60;width:100%;' : 'width:'+clampWidth(drawerWidth)+'px;min-width:'+clampWidth(drawerWidth)+'px;max-width:'+clampWidth(drawerWidth)+'px;flex:0 0 '+clampWidth(drawerWidth)+'px;'"
 @keydown.escape.window="if ($wire.formOpen) $wire.closeForm()">
 <div class="absolute inset-y-0 left-0 z-30 hidden w-5 cursor-col-resize touch-none md:block" role="separator" aria-orientation="vertical" aria-label="Arrastrar para cambiar el ancho del panel" @pointerdown="beginResize($event)" @pointermove="resizeTo($event)" @pointerup="finishResize()" @pointercancel="finishResize()" @dblclick="drawerWidth=520;resizing=true;finishResize()">
  <div class="pointer-events-none absolute left-1/2 top-1/2 flex h-16 w-8 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-md border border-blue-300 bg-white text-blue-600 shadow-md dark:border-blue-600 dark:bg-slate-800 dark:text-blue-300" aria-hidden="true"><x-ui.icon name="grip-vertical" /></div>
 </div>
<form wire:submit.prevent="save" x-on:input.capture="$wire.markRecordDirty()" x-on:change.capture="$wire.markRecordDirty()" class="flex h-full min-h-0 flex-col">
 <header class="relative z-10 flex h-12 min-h-12 shrink-0 items-center justify-end border-b border-slate-200 bg-white px-3 dark:border-slate-700 dark:bg-slate-900" style="height:48px;min-height:48px;flex-shrink:0;">
  <button type="button" wire:click="closeForm" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-red-600 hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-500 dark:text-red-400 dark:hover:bg-red-950" aria-label="Cerrar panel" title="Cerrar"><x-ui.icon name="x" class="h-6 w-6" /></button>
 </header>
 <div class="ops-fields min-h-0 flex-1 overflow-y-auto px-5 py-4" style="display:grid!important;grid-template-columns:minmax(0,1fr)!important;align-content:start!important;gap:14px!important;min-height:0;overflow-y:auto;">
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
 </div><footer class="flex shrink-0 items-center justify-end gap-2 border-t border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Guardar cambios</button></footer></form>
</aside>
</div>
<x-operations.view-settings :open="$viewSettingsOpen" :columns="$availableColumns"
    :visible="$visibleColumns"
    :current-view="$operationViews->firstWhere('id', $currentViewId)" :view-save-state="$viewSaveState" />
</section>
