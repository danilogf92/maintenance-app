<section class="ops-page flex h-full min-h-0 w-full flex-1 flex-col overflow-hidden" x-data="{showFilters:false}">
<x-operations.workspace-navigation :views="$operationViews" :active-id="$currentViewId">
    <x-slot:search><input type="search" placeholder="Buscar instituciones..." aria-label="Buscar instituciones..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:delete>
        <button type="button" class="ops-icon-button text-red-600 disabled:opacity-30" wire:click="requestDelete" @disabled(count($selectedIds) === 0) aria-label="Eliminar seleccionados" title="Eliminar seleccionados"><x-ui.icon name="trash" /></button>
    </x-slot:delete>
    <x-slot:create><button type="button" class="ops-icon-button" wire:click="create" aria-label="Nueva institución" title="Nueva institución"><x-ui.icon name="plus" /></button></x-slot:create>
</x-operations.workspace-navigation>
@if($confirmDelete)
<div class="ops-delete-confirm" role="alertdialog" aria-modal="true" aria-label="Confirmar eliminación">
 <span>¿Eliminar {{ count($selectedIds) }} registro(s) seleccionados? Esta acción no se puede deshacer.</span>
 <div class="flex gap-2"><button type="button" wire:click="cancelDelete">Cancelar</button><button type="button" class="ops-delete-danger" wire:click="deleteSelected">Eliminar</button></div>
</div>
@endif
@if(session('error'))<div class="ops-error" role="alert">{{ session('error') }}</div>@endif
@if(session('success'))<div class="ops-success" role="status">{{ session('success') }}</div>@endif
<div class="ops-toolbar ops-filter-panel" x-show="showFilters" x-cloak><select aria-label="Filtrar tipo" wire:model.live="typeFilter"><option value="">Todos los tipos</option><option value="company">Empresa</option><option value="hospital">Hospital</option><option value="clinic">Clínica</option><option value="university">Universidad</option><option value="other">Otro</option></select></div>
<div class="relative flex min-h-0 w-full flex-1 overflow-hidden" style="flex:1 1 0%;min-height:0;height:100%;" x-data="{drawerWidth:560,resizing:false,isMobile:window.innerWidth<768,storageKey:'maintenance-app:operation-drawer-width', init(){try {const v=Number(localStorage.getItem(this.storageKey));if(Number.isFinite(v)&&v>=320)this.drawerWidth=v;}catch(e){}}, clampWidth(v){return Math.max(320,Math.min(v,Math.max(320,window.innerWidth-240)))}, resizeTo(clientX){if(!this.resizing)return;this.drawerWidth=this.clampWidth(this.$el.getBoundingClientRect().right-clientX)}, finishResize(){if(!this.resizing)return;this.resizing=false;try{localStorage.setItem(this.storageKey,String(Math.round(this.drawerWidth)))}catch(e){}}, syncWidth(event){if(event.key!==this.storageKey||this.resizing)return;const v=Number(event.newValue);if(Number.isFinite(v)&&v>=320)this.drawerWidth=v}}" @resize.window="isMobile=window.innerWidth<768" @pointermove.window="resizeTo($event.clientX)" @pointerup.window="finishResize()" @pointercancel.window="finishResize()" @storage.window="syncWidth($event)">
<div class="ops-table-wrap min-h-0 min-w-0 flex-1 overflow-auto" style="height:auto!important;min-height:0!important;flex:1 1 0%!important;" x-ref="records"><table class="ops-table"><thead><tr>
@foreach($visibleColumns as $column)<th wire:key="head-{{ $column }}">{{ $availableColumns[$column] ?? $column }}</th>@endforeach
</tr></thead><tbody>
@forelse($items as $item)
<tr wire:key="institution-{{ $item->id }}" wire:click="edit({{ $item->id }})" class="cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-800" tabindex="0" @keydown.enter="$wire.edit({{ $item->id }})">
@foreach($visibleColumns as $column)
<td wire:key="cell-{{ $item->id }}-{{ $column }}" data-label="{{ $availableColumns[$column] ?? $column }}">
@switch($column)
@case('name') <strong>{{ $item->name }}</strong> @break
@case('code') {{ $item->code }} @break
@case('type') {{ ucfirst($item->type ?? 'other') }} @break
@case('city') {{ $item->city ?: '—' }} @break
@case('users') {{ $item->users_count }} @break
@case('id') {{ $item->id }} @break
@case('contact_email') {{ $item->contact_email ?: '—' }} @break
@case('contact_phone') {{ $item->contact_phone ?: '—' }} @break
@case('created_at') {{ $item->created_at?->format('d/m/Y H:i') ?? '—' }} @break
@case('updated_at') {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }} @break
@endswitch
</td>
@endforeach

</tr>
@empty<tr><td colspan="{{ count($visibleColumns) }}" class="ops-empty">No se encontraron registros.</td></tr>@endforelse
</tbody></table></div>

<aside x-show="$wire.formOpen" x-cloak x-transition.opacity.duration.150ms
 class="relative flex h-full min-h-0 shrink-0 flex-col border-l border-slate-200 bg-slate-50 text-slate-900 shadow-xl dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
 :style="isMobile ? 'position:fixed;inset:0;z-index:60;width:100%;' : 'width:'+clampWidth(drawerWidth)+'px'"
 @keydown.escape.window="if ($wire.formOpen) $wire.closeForm()">
 <div class="absolute inset-y-0 left-0 z-30 hidden w-3 cursor-col-resize touch-none md:block" role="separator" aria-orientation="vertical" aria-label="Arrastrar para cambiar el ancho del panel" @pointerdown.prevent="resizing=true" @dblclick="drawerWidth=560;resizing=true;finishResize()">
  <div class="absolute left-1/2 top-1/2 flex h-12 w-6 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-md border border-blue-300 bg-white text-blue-600 shadow-md dark:border-blue-600 dark:bg-slate-800 dark:text-blue-300" aria-hidden="true"><x-ui.icon name="grip-vertical" /></div>
 </div>

 <form wire:submit.prevent="save" class="flex h-full min-h-0 flex-col">
 <button type="button" wire:click="closeForm" class="absolute right-3 top-3 z-30 rounded-md p-2 text-red-600 hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-500 dark:text-red-400 dark:hover:bg-red-950" aria-label="Cerrar panel" title="Cerrar"><x-ui.icon name="x" /></button>
 <div class="ops-fields grid min-h-0 flex-1 grid-cols-1 content-start gap-3 overflow-y-auto px-5 pb-5 pt-14">
 <label>Nombre *<input wire:model="name" required maxlength="255">@error('name')<small>{{ $message }}</small>@enderror</label>
 <label>Código *<input wire:model="code" required maxlength="255">@error('code')<small>{{ $message }}</small>@enderror</label>
 <label>Tipo<select wire:model="type"><option value="company">Empresa</option><option value="hospital">Hospital</option><option value="clinic">Clínica</option><option value="university">Universidad</option><option value="other">Otro</option></select>@error('type')<small>{{ $message }}</small>@enderror</label>
 <label>Ciudad<input wire:model="city" maxlength="120">@error('city')<small>{{ $message }}</small>@enderror</label>
 <label>Correo de contacto<input type="email" wire:model="contact_email">@error('contact_email')<small>{{ $message }}</small>@enderror</label>
 <label>Teléfono<input wire:model="contact_phone" maxlength="80">@error('contact_phone')<small>{{ $message }}</small>@enderror</label>
 </div><footer class="flex shrink-0 items-center justify-end gap-2 border-t border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Guardar cambios</button></footer></form>
</aside>
</div>
<x-operations.view-settings :open="$viewSettingsOpen" :columns="$availableColumns"
    :visible="$visibleColumns"
    :current-view="$operationViews->firstWhere('id', $currentViewId)" :view-save-state="$viewSaveState" />
</section>
