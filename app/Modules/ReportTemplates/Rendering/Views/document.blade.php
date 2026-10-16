<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Informe técnico</title>
@include('report-print::styles')
</head>
<body>
<div class="running-footer">{{ $page['footer'] }}</div>
@if($repeatedHeader)<div class="running-header" data-preview-block="{{ $repeatedHeader['preview_index'] }}">@include('report-print::Blocks.header', ['block' => $repeatedHeader])</div>@endif
@if($page['repeat_brand'] && !$repeatedHeader)<div class="running-brand">{{ $brandName }} · Informe técnico</div>@endif
@foreach($blocks as $block)
    @if(!$block['enabled'] || ($repeatedHeader && $block === $repeatedHeader)) @continue @endif
    @php($type = $block['type'])
    @if($block['hide_empty'] && in_array($type, ['activities','components','parts','recommendations']) && empty($data[$type])) @continue @endif
    <section data-preview-block="{{ $block['preview_index'] }}" class="report-block {{ $block['page_break'] ? 'new-page' : '' }}" style="@if($block['font_size'] ?? null)--report-font-size:{{ $block['font_size'] }}pt;font-size:{{ $block['font_size'] }}pt;@endif margin-bottom:{{ $block['spacing'] }}mm">
    @if($type === 'header')
        @include('report-print::Blocks.header')
    @elseif(in_array($type, ['institution','equipment']))
        @include('report-print::Blocks.fields')
    @elseif(in_array($type, ['activities','components']))
        @include('report-print::Blocks.activities', ['rows' => $data[$type] ?? []])
    @elseif($type === 'separator')
        @include('report-print::Blocks.separator')
    @elseif($type === 'data_table')
        @include('report-print::Blocks.data-table')
    @elseif($type === 'parts')
        @include('report-print::Blocks.parts')
    @elseif($type === 'recommendations')
        @include('report-print::Blocks.recommendations')
    @elseif($type === 'text')
        @include('report-print::Blocks.title')
        {!! \App\Modules\ReportTemplates\Rendering\SafeRichText::render($block['text'] ?? '') !!}
    @elseif($type === 'signature')
        @include('report-print::Blocks.title')
        <div style="text-align:{{ $block['signature']['alignment'] ?? 'left' }}">@include('report-print::Blocks.signature-field', ['signature' => $block['signature']])</div>
    @elseif($type === 'signatures')
        @include('report-print::Blocks.signatures')
    @endif
    </section>
@endforeach
</body>
</html>
