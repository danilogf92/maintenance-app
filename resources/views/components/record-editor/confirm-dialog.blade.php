{{-- View deletion uses the same modal as record deletion. --}}
<div x-data="{open:false}"
    @confirm-operation.window="if ($event.detail.action === 'deleteView') open=true"
    @deletion-dialog-closed.stop="open=false">
    <x-ui.deletion-modal state="open" title="Eliminar vista" confirm-action="deleteView"
        message="Se eliminará esta vista. Los datos de la tabla no se eliminarán." />
</div>
