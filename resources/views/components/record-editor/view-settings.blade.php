@props(['open', 'columns', 'visible', 'currentView', 'viewSaveState', 'favoriteId','tableColumns','columnOptions','tableFilters','tableSort','tableDirection','tableGroup'])
@if($open)
<div wire:key="view-settings-modal" x-data="{init(){this.$nextTick(()=>{if(!this.$refs.viewDialog.open)this.$refs.viewDialog.showModal()})},destroy(){this.$refs.viewDialog?.close()}}">
    <dialog x-ref="viewDialog" wire:ignore.self class="ops-modal ops-view-dialog" aria-labelledby="ops-view-settings-title"
     @cancel.prevent="$wire.closeViewSettings()" @keydown.escape.stop @click="if($event.target===$refs.viewDialog)$wire.closeViewSettings()">
        <header class="ops-modal-header">
            <div><h2 id="ops-view-settings-title">Configurar vista</h2><p>Columnas y nombre de la pestaña</p></div>
            <button type="button" wire:click="closeViewSettings" aria-label="Cerrar configuración" title="Cerrar (Esc)"
                class="group !inline-flex !h-10 !w-10 !items-center !justify-center !rounded-lg !bg-red-50 !text-red-600 !transition-colors hover:!bg-red-600 hover:!text-white focus-visible:!outline focus-visible:!outline-2 focus-visible:!outline-red-600 dark:!bg-red-950 dark:!text-red-400 dark:hover:!bg-red-600 dark:hover:!text-white">
                <x-ui.icon name="x" class="!h-5 !w-5" />
            </button>
        </header>
        <div class="ops-modal-content">
            <label class="ops-modal-label" for="ops-view-name">Nombre de la vista</label>
            <input id="ops-view-name" class="ops-view-name" type="text" maxlength="100"
                wire:model.live.debounce.650ms="viewName" autocomplete="off">
            @error('viewName')<p class="ops-field-error">{{ $message }}</p>@enderror
            <div class="ops-column-toolbar">
                <button type="button" wire:click="showAllColumns" class="ops-column-button" wire:loading.attr="disabled"><x-ui.icon name="eye" />Mostrar todas</button>
                <button type="button" wire:click="resetColumns" class="ops-column-button" wire:loading.attr="disabled"><x-ui.icon name="reset" />Restablecer columnas</button>
            </div>
            <button type="button" class="ops-column-button ops-favorite-button {{ $favoriteId === $currentView?->id ? 'is-favorite' : '' }}"
                wire:click="chooseFavorite({{ $currentView?->id }})" wire:loading.attr="disabled" wire:target="chooseFavorite"
                aria-pressed="{{ $favoriteId === $currentView?->id ? 'true' : 'false' }}">
                <x-ui.icon name="star" />{{ $favoriteId === $currentView?->id ? 'Vista favorita · abre al entrar' : 'Abrir esta vista al entrar' }}
            </button>
            <div class="ops-column-table-wrap">
             <table class="ops-column-table"><thead><tr><th>Columna</th><th>Visible</th><th>Orden</th></tr></thead><tbody>
              @foreach(array_unique([...$visible,...array_keys($columns)]) as $key)
               @if(isset($columns[$key]))
               @php($position = array_search($key,$visible,true))
               <tr wire:key="column-setting-{{ $key }}">
                <x-record-editor.column-header tag="td" :column="$key" :label="$columns[$key]" :definition="$tableColumns[$key]"
                 :options="$columnOptions[$key] ?? []" :filter="$tableFilters[$key] ?? ''" :sort="$tableSort" :direction="$tableDirection" :group="$tableGroup" />
                <td><button type="button" class="ops-column-visibility" role="switch" aria-checked="{{ $position !== false ? 'true' : 'false' }}"
                 wire:click="toggleColumn('{{ $key }}')" aria-label="Mostrar {{ $columns[$key] }}" @disabled(count($visible)===1 && $position!==false)>
                 <x-ui.icon :name="$position !== false ? 'eye' : 'eye-off'" /></button></td>
                <td><div class="ops-column-actions">
                 @if($position!==false)
                 <span class="ops-column-position">{{ $position+1 }}</span>
                 <button type="button" wire:click="moveColumn('{{ $key }}',-1)" aria-label="Subir {{ $columns[$key] }}" @disabled($position===0)><x-ui.icon name="arrow-up" /></button>
                 <button type="button" wire:click="moveColumn('{{ $key }}',1)" aria-label="Bajar {{ $columns[$key] }}" @disabled($position===count($visible)-1)><x-ui.icon name="arrow-down" /></button>
                 @else<span>—</span>@endif
                </div></td>
               </tr>
               @endif
              @endforeach
             </tbody></table>
            </div>
            @error('visibleColumns')<p class="ops-field-error">{{ $message }}</p>@enderror
        </div>
        <footer class="ops-modal-footer" aria-live="polite">
            <span wire:loading wire:target="viewName,visibleColumns,toggleColumn,moveColumn,saveViewSettings">◌ Guardando…</span>
            <span wire:loading.remove wire:target="viewName,visibleColumns,toggleColumn,moveColumn,saveViewSettings">
                @if($viewSaveState === 'saved')<span class="ops-saved">✓ Guardado</span>
                @elseif($viewSaveState === 'error')<span class="ops-field-error">No se pudo guardar</span>@endif
            </span>
            @if($currentView?->is_default === false)
                <button type="button" class="ops-delete-view ops-column-button ops-column-danger" @click="$dispatch('confirm-operation', { title: 'Eliminar vista', message: 'Los datos de la tabla no se eliminarán.', action: 'deleteView' })">Eliminar vista</button>
            @endif
        </footer>
    </dialog>
    <x-record-editor.confirm-dialog />
</div>
@endif
