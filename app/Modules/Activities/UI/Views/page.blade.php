<section class="ops-page flex h-full min-h-0 w-full flex-1 flex-col overflow-hidden" x-data="{}">
    <link rel="stylesheet" href="{{ asset('css/pages/activity-editor.css') }}?v=11.46.0">
    @include('components.record-editor.view-settings-styles')
    @include('activities::toolbar')
    <x-record-editor.delete-confirmation :open="$confirmDelete" :record-name="$recordDeletionName" />
    @if(session('error'))<div class="ops-error" role="alert">{{ session('error') }}</div>@endif
    <x-record-editor.row-styles />
    <x-record-editor.table-tools-styles />
    <x-record-editor.table-tools-status :columns="$availableColumns" :filters="$tableFilters"
        :sort="$tableSort" :direction="$tableDirection" :group="$tableGroup" />
    <x-record-editor.drawer-workspace>
        @include('activities::table')
        @include('activities::editor')
    </x-record-editor.drawer-workspace>
<x-record-editor.view-settings :open="$viewSettingsOpen" :columns="$availableColumns"
    :visible="$visibleColumns"
    :current-view="$operationViews->firstWhere('id', $currentViewId)" :view-save-state="$viewSaveState" :favorite-id="$favoriteViewId"
    :table-columns="$tableColumns" :column-options="$columnOptions" :table-filters="$tableFilters"
    :table-sort="$tableSort" :table-direction="$tableDirection" :table-group="$tableGroup" />
 @if($pendingPhotoRemoval)<x-ui.deletion-modal state="Object.keys($wire.pendingPhotoRemoval).length > 0" title="Retirar fotografía" confirm-label="Retirar" confirm-action="confirmRemovePhoto" cancel-action="cancelPhotoRemoval" message="Se retirará esta fotografía de la actividad. El archivo original se conserva en el almacenamiento privado." />@endif
</section>
