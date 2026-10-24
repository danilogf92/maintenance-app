<x-record-editor.tabbed-editor-styles />
<aside wire:ignore.self x-show.important="$wire.formOpen" x-cloak
 class="ops-detail-panel user-editor relative flex h-full min-h-0 shrink-0 flex-col border-l border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
 :style="panelStyle()" style="{{ $editorLayoutStyle }}"
 x-data="{activeTab:@js(array_key_first($editorTabs)),init(){this.$watch('$wire.editorSession',()=>this.activeTab=@js(array_key_first($editorTabs)));}}"
 @keydown.escape.window="if ($wire.formOpen) $wire.closeForm()">
 <x-record-editor.record-form novalidate>
  <header class="record-editor-header">
   <x-record-editor.mobile-title label="Usuario" :record-id="$editingId" />
   <x-record-editor.drawer-handle />
   <x-record-editor.record-save-status />
   <x-record-editor.drawer-close />
  </header>
  @include('users::tabs')
  @include('users::sections')
 </x-record-editor.record-form>
</aside>
