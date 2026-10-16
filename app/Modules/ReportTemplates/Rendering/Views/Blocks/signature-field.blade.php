@php
    $signature = array_replace(\App\Modules\ReportTemplates\Configuration\Signature::defaults(), $signature ?? []);
    $signatureImage = \App\Modules\ReportTemplates\Rendering\SignatureRenderer::image($data, $signature['source']);
    $signatureName = ($signature['name_variable'] ?? '') ? \App\Modules\ReportTemplates\Configuration\Variables::value($data, $signature['name_variable']) : ($signature['name'] ?? '');
@endphp
<span class="individual-signature" style="width:{{ $signature['width'] }}mm;{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css($signature['style'] ?? []) }}">
    <span class="signature-space" style="height:{{ $signature['height'] }}mm;@if($signature['show_line'])border-bottom:.3mm solid {{ $signature['line_color'] }};@endif">
        @if($signatureImage)<img src="{{ $signatureImage }}" alt="Firma de este informe">@endif
    </span>
    @if($signature['show_name'])<span class="signature-name">{{ $signatureName }}</span>@endif
    @if($signature['caption'] ?? '')<span class="signature-caption">{{ $signature['caption'] }}</span>@endif
</span>
