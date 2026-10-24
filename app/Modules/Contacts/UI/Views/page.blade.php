<section class="ops-page flex h-full min-h-0 w-full flex-1 flex-col overflow-hidden" x-data="{}">
    @include('components.record-editor.view-settings-styles')
    @include('contacts::toolbar')
    <x-record-editor.delete-confirmation :open="$confirmDelete" :record-name="$recordDeletionName" />
    @if(session('error'))<div class="ops-error" role="alert">{{ session('error') }}</div>@endif
    <x-record-editor.row-styles />
    <x-record-editor.table-tools-styles />
    <x-record-editor.table-tools-status :columns="$availableColumns" :filters="$tableFilters"
        :sort="$tableSort" :direction="$tableDirection" :group="$tableGroup" />
    <x-record-editor.drawer-workspace>
        @include('contacts::table')
        @include('contacts::editor')
    </x-record-editor.drawer-workspace>
<x-record-editor.view-settings :open="$viewSettingsOpen" :columns="$availableColumns"
    :visible="$visibleColumns"
    :current-view="$operationViews->firstWhere('id', $currentViewId)" :view-save-state="$viewSaveState" :favorite-id="$favoriteViewId"
    :table-columns="$tableColumns" :column-options="$columnOptions" :table-filters="$tableFilters"
    :table-sort="$tableSort" :table-direction="$tableDirection" :table-group="$tableGroup" />
</section>
