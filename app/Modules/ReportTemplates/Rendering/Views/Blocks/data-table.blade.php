@include('report-print::Blocks.title')
@php
    $sections = array_values($block['data_sections']);
    $rowCount = max(array_map(fn ($section) => count($section['fields']), $sections));
@endphp
<table class="data-columns {{ ($block['bordered'] ?? false) ? 'with-borders' : '' }}">
@if($block['show_table_header'] ?? true)
<thead @if(!$block['repeat_header']) style="display:table-row-group" @endif><tr>
@foreach($sections as $sectionIndex => $section)
<th data-preview-data-column="{{ $sectionIndex }}">@if($section['show_title'])<h3 class="data-section-title" style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css(['background' => $block['section_background'], 'color' => $block['section_color']], $block['table_header_style'] ?? [], $section['title_style'] ?? []) }}">{{ $section['title'] }}</h3>@endif</th>
@endforeach
</tr></thead>
@endif
<tbody>
@for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
<tr>
@foreach($sections as $sectionIndex => $section)
    @php
        $field = array_values($section['fields'])[$rowIndex] ?? null;
    @endphp
    <td data-preview-data-column="{{ $sectionIndex }}" @if($field) data-preview-data-row="{{ $rowIndex }}" @endif>
    @if($field)
        @php
            $label = trim($field['label'] ?? '') ?: (\App\Modules\ReportTemplates\Configuration\Variables::options()[$field['variable'] ?? ''] ?? '');
            $alignment = ($field['item_alignment'] ?? 'inherit') === 'inherit' ? $section['item_alignment'] : $field['item_alignment'];
            $value = $field['mode'] === 'manual' ? ($field['text'] ?? '') : \App\Modules\ReportTemplates\Configuration\Variables::value($data, $field['variable'] ?? '');
        @endphp
        @if($alignment !== 'legacy')
            <div class="data-item" data-item-alignment="{{ $alignment }}" style="text-align:{{ $alignment }};padding-left:{{ 1 + $field['item_indent'] }}mm">
                @if($block['show_labels'])<span class="data-item-label" style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css(['color' => $block['label_color']], $section['label_style'] ?? [], $field['label_style'] ?? []) }}">{{ $label }}</span>@endif<span class="data-value" style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css(['color' => $block['value_color']], $section['value_style'] ?? [], $field['value_style'] ?? []) }}">@include('report-print::Blocks.data-value')</span>
            </div>
        @else
        <table class="data-values"><tr>
            @if($block['show_labels'])<td class="data-label" style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css(['color' => $block['label_color']], $section['label_style'] ?? [], $field['label_style'] ?? []) }}">{{ $label }}</td>@endif
            <td class="data-value" style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css(['color' => $block['value_color']], $section['value_style'] ?? [], $field['value_style'] ?? []) }}">@include('report-print::Blocks.data-value')</td>
        </tr></table>
        @endif
    @endif
    </td>
@endforeach
</tr>
@endfor
</tbody></table>
