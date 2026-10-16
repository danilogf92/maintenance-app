@if($field['mode'] === 'signature')
    @include('report-print::Blocks.signature-field', ['signature' => $field['signature']])
@else{{ $value }}@endif
