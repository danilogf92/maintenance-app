<x-record-editor.workspace-navigation :views="$operationViews" :active-id="$currentViewId" :favorite-id="$favoriteViewId">
    <x-slot:search><input type="search" placeholder="Buscar equipos..." aria-label="Buscar equipos..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:recordNavigation>
        <x-record-editor.record-navigation />
    </x-slot:recordNavigation>
    <x-slot:create>@if($canManage)<button type="button" class="ops-icon-button ops-record-create" wire:click="create" aria-label="Nuevo equipo" title="Nuevo equipo"><x-ui.icon name="plus" /></button>@endif</x-slot:create>
</x-record-editor.workspace-navigation>
