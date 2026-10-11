@props(['open', 'recordName'])
@if($open)
<x-ui.deletion-modal state="$wire.confirmDelete" :record-name="$recordName"
    confirm-action="deleteConfirmedRecord" cancel-action="cancelDelete" />
@endif
