<x-record-editor.workspace-navigation :views="$operationViews" :active-id="$currentViewId" :favorite-id="$favoriteViewId">
    <x-slot:search><input type="search" placeholder="Buscar usuarios..." aria-label="Buscar usuarios..." wire:model.live.debounce.350ms="search"></x-slot:search>
    <x-slot:delete>
        <button type="button" class="ops-icon-button ops-record-delete text-red-600 disabled:opacity-30" wire:click="requestOpenRecordDelete" @disabled(!$formOpen || !$editingId) aria-label="Eliminar registro abierto" title="Eliminar registro abierto"><x-ui.icon name="trash" /></button>
    </x-slot:delete>
    <x-slot:recordNavigation>
        <x-record-editor.record-navigation />
    </x-slot:recordNavigation>
    <x-slot:create><button type="button" class="ops-icon-button ops-record-create" wire:click="create" aria-label="Nuevo usuario" title="Nuevo usuario"><x-ui.icon name="plus" /></button></x-slot:create>
</x-record-editor.workspace-navigation>
