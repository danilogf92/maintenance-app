<x-record-editor.workspace-navigation :views="$operationViews" :active-id="$currentViewId" :favorite-id="$favoriteViewId">
    <x-slot:search><input type="search" placeholder="Buscar protocolos de mantenimiento..." aria-label="Buscar protocolos de mantenimiento..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:recordNavigation>
        <button type="button" wire:click="$toggle('showPending')" class="ops-btn" aria-pressed="{{ $showPending ? 'true' : 'false' }}">{{ $showPending ? 'Ver protocolos' : 'Pasos pendientes ('.$pendingCount.')' }}</button>
        <x-record-editor.record-navigation />
    </x-slot:recordNavigation>

</x-record-editor.workspace-navigation>
