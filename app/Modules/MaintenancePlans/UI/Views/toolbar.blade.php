<x-record-editor.workspace-navigation :views="$operationViews" :active-id="$currentViewId" :favorite-id="$favoriteViewId">
    <x-slot:search><input type="search" placeholder="Buscar planes de mantenimiento..." aria-label="Buscar planes de mantenimiento..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:recordNavigation>
        <x-record-editor.record-navigation />
    </x-slot:recordNavigation>
    <x-slot:create><a href="{{ route('operations.maintenance_calendar') }}" class="ops-icon-button" aria-label="Calendario de planificación" title="Calendario de planificación"><x-ui.icon name="calendar" /></a>@if($canManage)<button type="button" class="ops-icon-button ops-record-create" wire:click="create" aria-label="Nuevo plan de mantenimiento" title="Nuevo plan de mantenimiento"><x-ui.icon name="plus" /></button>@endif</x-slot:create>
</x-record-editor.workspace-navigation>
