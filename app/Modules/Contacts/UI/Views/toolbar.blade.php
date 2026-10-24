<x-record-editor.workspace-navigation :views="$operationViews" :active-id="$currentViewId" :favorite-id="$favoriteViewId">
    <x-slot:search><input type="search" placeholder="Buscar contactos..." aria-label="Buscar contactos..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:recordNavigation>
        <x-record-editor.record-navigation />
    </x-slot:recordNavigation>
    <x-slot:create>@if($canManage)<button type="button" class="ops-icon-button ops-record-create" wire:click="create" aria-label="Nuevo contacto" title="Nuevo contacto"><x-ui.icon name="plus" /></button>@endif</x-slot:create>
</x-record-editor.workspace-navigation>
