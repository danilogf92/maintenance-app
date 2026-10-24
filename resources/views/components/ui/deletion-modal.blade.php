@props(['state' => 'open', 'title' => 'Eliminar registro', 'recordName' => null,
    'message' => 'Esta acción no se puede deshacer.',
    'confirmLabel' => 'Eliminar', 'confirmAction' => 'deleteConfirmedRecord', 'cancelAction' => null])
{{-- A native dialog appears above every sidebar, editor and settings modal. --}}
<dialog wire:ignore.self class="deletion-modal" role="alertdialog" aria-modal="true"
    x-id="['deletion-title','deletion-description']"
    :aria-labelledby="$id('deletion-title')" :aria-describedby="$id('deletion-description')"
    x-data="{
        modalElement:null, triggerElement:null, busy:false, errorMessage:'',
        init() { this.modalElement=this.$el; this.triggerElement=document.activeElement; },
        syncModal(show) {
            const modal=this.modalElement;
            if (!modal?.isConnected) return;
            if (show && !modal.open) modal.showModal();
            else if (!show && modal.open) modal.close();
        },
        async accept() {
            if (this.busy) return;
            this.busy=true; this.errorMessage='';
            try {
                await this.$wire[@js($confirmAction)]();
                this.$dispatch('deletion-dialog-closed');
                this.modalElement.close();
            } catch (error) {
                this.errorMessage='No se pudo eliminar. Revisa tus permisos y vuelve a intentarlo.';
            } finally { this.busy=false; }
        },
        async dismiss() {
            if (this.busy) return;
            this.busy=true; this.errorMessage='';
            try {
                const action=@js($cancelAction);
                if (action) await this.$wire[action]();
                this.$dispatch('deletion-dialog-closed');
                this.modalElement.close();
            } catch (error) { this.errorMessage='No se pudo cerrar. Vuelve a intentarlo.'; }
            finally { this.busy=false; }
        },
        destroy() {
            if (this.modalElement?.open) this.modalElement.close();
            if (this.triggerElement?.isConnected) this.triggerElement.focus();
        }
    }"
    x-effect="syncModal({{ $state }})"
    @keydown.escape.prevent.stop="dismiss()" @cancel.prevent="dismiss()"
    @click.self="dismiss()" {{ $attributes }}>
    <header class="deletion-modal-heading">
        <span class="deletion-modal-icon"><x-ui.icon name="trash" /></span>
        <button type="button" class="deletion-modal-close" aria-label="Cerrar confirmación"
            title="Cerrar" @click="dismiss()" :disabled="busy"><x-ui.icon name="x" /></button>
    </header>
    <div class="deletion-modal-content">
        <h2 :id="$id('deletion-title')">{{ $title }}</h2>
        <div :id="$id('deletion-description')">
            @if($recordName)<p class="deletion-modal-record">{{ $recordName }}</p>@endif
            <p class="deletion-modal-help">{{ $message }}</p>
        </div>
        <p class="deletion-modal-error" role="alert" x-show="errorMessage" x-text="errorMessage"></p>
    </div>
    <footer class="deletion-modal-actions">
        <button type="button" class="deletion-modal-cancel" @click="dismiss()" :disabled="busy" autofocus>Cancelar</button>
        <button type="button" class="deletion-modal-confirm" @click="accept()" :disabled="busy">
            <span x-text="busy ? 'Procesando…' : @js($confirmLabel)">{{ $confirmLabel }}</span>
        </button>
    </footer>
</dialog>
