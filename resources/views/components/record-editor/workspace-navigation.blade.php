@props(['views', 'activeId', 'favoriteId'])
<div class="ops-view-tabs" role="tablist" aria-label="Vistas guardadas">
    @foreach($views as $view)
        <button type="button" role="tab" wire:key="view-tab-{{ $view->id }}"
            @click="if (Number($wire.currentViewId) !== {{ $view->id }}) $wire.selectView({{ $view->id }})"
            wire:loading.attr="disabled" wire:target="selectView"
            x-on:contextmenu.prevent="$wire.openViewSettingsFor({{ $view->id }})"
            title="{{ $view->name }} · clic derecho para configurar"
            class="ops-view-tab {{ $activeId === $view->id ? 'ops-view-tab-active' : '' }}"
            aria-selected="{{ $activeId === $view->id ? 'true' : 'false' }}">
            @if($favoriteId === $view->id)<x-ui.icon name="star" class="ops-tab-star" /><span class="sr-only">Favorita: </span>@endif
            {{ $view->name }}
        </button>
    @endforeach
    <button type="button" class="ops-view-tab ops-add-tab" wire:click="addView"
        title="Crear vista" aria-label="Crear vista"><x-ui.icon name="plus" /></button>
</div>
<div class="ops-view-actions" aria-label="Herramientas de tabla">
    <div class="ops-tools-left">
        <button type="button" class="ops-icon-button" wire:click="openViewSettings"
            title="Configurar vista" aria-label="Configurar vista"><x-ui.icon name="table" /></button>
        <div class="ops-quick-search">{{ $search }}</div>
    </div>
    <div class="ops-view-nav">
        <button type="button" class="ops-record-print" title="Imprimir" aria-label="Imprimir" @click="window.print()"><x-ui.icon name="printer" /></button>
        @isset($delete){{ $delete }}@endisset
        @isset($create){{ $create }}@endisset
        <span class="ops-tool-separator" aria-hidden="true"></span>
        @isset($recordNavigation)
            {{ $recordNavigation }}
        @else
            <x-record-editor.record-navigation />
        @endisset
    </div>
</div>
