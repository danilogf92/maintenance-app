@include('report-print::Blocks.title')
<ul>@foreach($data['recommendations'] ?? [] as $recommendation)<li>{{ $recommendation }}</li>@endforeach</ul>
