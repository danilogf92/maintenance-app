@once
<link rel="stylesheet" href="{{ asset('css/pages/source-details.css') }}?v=11.52.2">
@endonce
<dl {{ $attributes->class(['record-source-details']) }}>{{ $slot }}</dl>
