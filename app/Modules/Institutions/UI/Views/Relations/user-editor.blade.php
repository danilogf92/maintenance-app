<div x-data="{dialog:null,activeTab:'general',init(){this.$watch('$wire.editorSession',()=>this.activeTab='general');this.dialog=this.$refs.userDialog;this.$watch('$wire.formOpen',()=>this.sync());this.sync();},sync(){if(this.$wire.formOpen && !this.dialog.open)this.dialog.showModal();else if(!this.$wire.formOpen && this.dialog.open)this.dialog.close();},destroy(){if(this.dialog?.open)this.dialog.close();}}">
 <x-record-editor.tabbed-editor-styles />
 <dialog style="{{ $editorLayoutStyle }}" x-ref="userDialog" class="institution-user-dialog" @cancel.prevent="$wire.closeForm()" aria-label="Editar usuario relacionado">
  <x-record-editor.record-form novalidate>
   <header class="record-editor-header"><strong style="margin-right:auto">Editar usuario</strong><x-record-editor.record-save-status /><x-record-editor.drawer-close /></header>
   @include('users::tabs')
   @include('users::sections')
  </x-record-editor.record-form>
 </dialog>
</div>
