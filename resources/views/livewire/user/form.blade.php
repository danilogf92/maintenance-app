<aside wire:ignore.self x-show.important="$wire.formOpen" x-cloak
    class="ops-detail-panel relative flex h-full min-h-0 shrink-0 flex-col overflow-visible border-l border-slate-200 bg-slate-50 text-slate-900 shadow-xl dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
    :style="panelStyle()" @keydown.escape.window="if ($wire.formOpen) $wire.closeForm()">
    <x-record-editor.record-form>
        <header
            class="relative z-10 flex h-12 min-h-12 shrink-0 items-center justify-end border-b border-slate-200 bg-white px-3 dark:border-slate-700 dark:bg-slate-900"
            style="height:48px;min-height:48px;flex-shrink:0;">
            <x-record-editor.mobile-title label="Usuario" :record-id="$editingId" />
            <x-record-editor.drawer-handle />
            <x-record-editor.record-save-status />
            <x-record-editor.drawer-close />
        </header>
        <div class="ops-fields min-h-0 flex-1 overflow-y-auto px-5 py-4"
            style="display:grid!important;grid-template-columns:minmax(0,1fr)!important;align-content:start!important;gap:14px!important;min-height:0;overflow-y:auto;">
            <x-record-editor.validation-summary />
            @include('livewire.user.fields')
        </div>
    </x-record-editor.record-form>
</aside>
