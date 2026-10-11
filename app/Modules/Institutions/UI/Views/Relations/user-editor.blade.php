<div x-data="{dialog:null,init(){this.dialog=this.$refs.userDialog;this.$watch('$wire.formOpen',()=>this.sync());this.sync();},sync(){if(this.$wire.formOpen && !this.dialog.open)this.dialog.showModal();else if(!this.$wire.formOpen && this.dialog.open)this.dialog.close();},destroy(){if(this.dialog?.open)this.dialog.close();}}">
 <dialog x-ref="userDialog" class="institution-user-dialog" @cancel.prevent="$wire.closeForm()" aria-label="Editar usuario relacionado">
  <x-record-editor.record-form novalidate>
   <header class="institution-editor-header"><strong style="margin-right:auto">Editar usuario</strong><x-record-editor.record-save-status /><x-record-editor.drawer-close /></header>
   <div class="ops-fields institution-user-fields" style="display:grid;grid-template-columns:1fr;gap:16px">
    <x-record-editor.validation-summary />
    @include('livewire.user.fields')
   </div>
  </x-record-editor.record-form>
 </dialog>
</div>
