@props(['open', 'columns', 'visible', 'currentView', 'viewSaveState', 'favoriteId'])
@if($open)
<div class="ops-modal-backdrop" role="presentation" wire:key="view-settings-modal"
    wire:click.self="closeViewSettings"
    x-on:keydown.escape.window="$wire.closeViewSettings()">
    <section class="ops-modal" role="dialog" aria-modal="true" aria-labelledby="ops-view-settings-title">
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
            <div class="ops-column-panels">
                <div><h3>Columnas visibles</h3>
                    @foreach($visible as $index => $key)
                        @if(isset($columns[$key]))
                        <div class="ops-column-item" wire:key="visible-{{ $key }}">
                            <span>{{ $columns[$key] }}</span>
                            <div class="ops-column-actions">
                                <button type="button" wire:click="moveColumn('{{ $key }}', -1)" aria-label="Subir {{ $columns[$key] }}" title="Subir columna" @disabled($index === 0)><x-ui.icon name="arrow-up" /></button>
                                <button type="button" wire:click="moveColumn('{{ $key }}', 1)" aria-label="Bajar {{ $columns[$key] }}" title="Bajar columna" @disabled($index === count($visible) - 1)><x-ui.icon name="arrow-down" /></button>
                                <button type="button" wire:click="toggleColumn('{{ $key }}')" class="ops-column-danger" aria-label="Ocultar {{ $columns[$key] }}" title="Ocultar columna" @disabled(count($visible) === 1)><x-ui.icon name="eye-off" /></button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div><h3>Columnas disponibles</h3>
                    @foreach($columns as $key => $label)
                        @unless(in_array($key, $visible, true))
                            <button type="button" class="ops-column-item ops-add-column" wire:key="available-{{ $key }}"
                                wire:click="toggleColumn('{{ $key }}')"><span>{{ $label }}</span><x-ui.icon name="plus" /></button>
                        @endunless
                    @endforeach
                </div>
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
    </section>
    <x-record-editor.confirm-dialog />
</div>
@endif
