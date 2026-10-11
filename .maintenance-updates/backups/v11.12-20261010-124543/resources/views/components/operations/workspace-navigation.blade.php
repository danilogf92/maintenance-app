@props(['views', 'activeId'])
<div class="ops-view-tabs" role="tablist" aria-label="Vistas guardadas">
    @foreach($views as $view)
        <button type="button" role="tab" wire:key="view-tab-{{ $view->id }}"
            wire:click="selectView({{ $view->id }})"
            x-on:contextmenu.prevent="$wire.openViewSettingsFor({{ $view->id }})"
            title="{{ $view->name }} · clic derecho para configurar"
            class="ops-view-tab {{ $activeId === $view->id ? 'ops-view-tab-active' : '' }}"
            aria-selected="{{ $activeId === $view->id ? 'true' : 'false' }}">
            {{ $view->name }}
        </button>
    @endforeach
    <button type="button" class="ops-view-tab ops-add-tab" wire:click="addView"
        title="Crear vista" aria-label="Crear vista"><x-ui.icon name="plus" /></button>
</div>
<div class="ops-view-actions" aria-label="Herramientas de tabla">
    <div class="ops-tools-left">
        <button type="button" class="ops-icon-button" wire:click="openViewSettings"
            title="Configurar vista" aria-label="Configurar vista"><x-ui.icon name="settings" /></button>
        <button type="button" class="ops-icon-button" @click="showFilters = !showFilters"
            title="Filtros" aria-label="Filtros"><x-ui.icon name="filter" /></button>
        <div class="ops-quick-search">{{ $search }}</div>
    </div>
    <div class="ops-view-nav">
        <button type="button" title="Imprimir" aria-label="Imprimir" @click="window.print()"><x-ui.icon name="printer" /></button>
        @isset($delete){{ $delete }}@endisset
        @isset($create){{ $create }}@endisset
        <span class="ops-tool-separator" aria-hidden="true"></span>
        <button type="button" title="Primero" aria-label="Primero" @click="$refs.records.scrollTo({top:0,behavior:'smooth'})"><x-ui.icon name="skip-back" /></button>
        <button type="button" title="Anterior" aria-label="Anterior" @click="$refs.records.scrollBy({top:-260,behavior:'smooth'})"><x-ui.icon name="play-back" /></button>
        <button type="button" title="Siguiente" aria-label="Siguiente" @click="$refs.records.scrollBy({top:260,behavior:'smooth'})"><x-ui.icon name="play-forward" /></button>
        <button type="button" title="Último" aria-label="Último" @click="$refs.records.scrollTo({top:$refs.records.scrollHeight,behavior:'smooth'})"><x-ui.icon name="skip-forward" /></button>
    </div>
</div>
