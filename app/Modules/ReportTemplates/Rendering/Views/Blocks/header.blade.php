<table class="configured-header"><tr>
@foreach($block['rendered_columns'] as $columnKey => $column)
<td data-preview-column="{{ $columnKey }}" style="width:{{ $column['percent'] }}%;text-align:{{ $column['alignment'] }};border:{{ $column['border'] ? '0.4mm solid '.$column['border_color'] : '0' }}">
    @if($column['image'])
        <img class="header-logo" src="{{ $column['image'] }}" alt="Logo" style="max-height:{{ $column['logo_height'] }}mm">
    @elseif($column['fallback'])
        <p class="brand-name">{{ $column['fallback'] }}</p>
    @endif
    @foreach($column['lines'] as $line)
        <p data-preview-line="{{ $loop->index }}" class="header-line" style="color:{{ $line['color'] }};font-size:{{ $line['size'] }}pt;font-weight:{{ $line['bold'] ? 'bold' : 'normal' }};{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css(array_filter(['font' => $line['font'] ?? null, 'italic' => $line['italic'] ?? null], fn ($v) => $v !== null)) }}">{{ $line['value'] }}</p>
    @endforeach
</td>
@endforeach
</tr></table>
