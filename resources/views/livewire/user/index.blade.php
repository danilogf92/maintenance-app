<section class="ops-page flex h-full min-h-0 w-full flex-1 flex-col overflow-hidden" x-data="{showFilters:false}">
    @include('components.record-editor.view-settings-styles')
    @include('livewire.user.toolbar')
    <x-record-editor.delete-confirmation :open="$confirmDelete" :record-name="$recordDeletionName" />
    @if(session('error'))<div class="ops-error" role="alert">{{ session('error') }}</div>@endif
    @include('livewire.user.filters')
    <x-record-editor.row-styles />
    <x-record-editor.table-tools-styles />
    <x-record-editor.table-tools-status :columns="$availableColumns" :filters="$tableFilters"
        :sort="$tableSort" :direction="$tableDirection" :group="$tableGroup" />
    <x-record-editor.drawer-workspace>
        @include('livewire.user.table')
        @include('livewire.user.form')
    </x-record-editor.drawer-workspace>
<x-record-editor.view-settings :open="$viewSettingsOpen" :columns="$availableColumns"
    :visible="$visibleColumns"
    :current-view="$operationViews->firstWhere('id', $currentViewId)" :view-save-state="$viewSaveState" :favorite-id="$favoriteViewId" />
</section>
