@include('report-print::Blocks.title')
<table>
<colgroup><col style="width:8mm">
@foreach($block['table_columns'] as $column)<col @if($column === 'photos' && count($block['table_columns']) > 1) style="width:50%" @endif>@endforeach
</colgroup>
@if($block['show_table_header'] ?? true)
<thead @if(!$block['repeat_header']) style="display:table-row-group" @endif><tr>
<th style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css($block['table_header_style'] ?? []) }}">#</th>
@foreach($block['table_columns'] as $column)<th style="{{ \App\Modules\ReportTemplates\Configuration\TextStyle::css($block['table_header_style'] ?? []) }}">{{ \App\Modules\ReportTemplates\Configuration\Blocks::tableOptions()[$column] }}</th>@endforeach
</tr></thead>
@endif
<tbody>
@foreach($rows as $rowIndex => $row)
    @php($chunks = array_chunk($row['photos'] ?? [], (int)$block['photo_columns']) ?: [[]])
    @foreach($chunks as $chunkIndex => $photos)
    <tr>
        <td>{{ $rowIndex + 1 }}</td>
        @foreach($block['table_columns'] as $column)
            <td>
            @if($column === 'photos')
                <table class="photos"><tr>
                @forelse($photos as $photo)
                    <td><figure class="photo-item">
                        @if($photo['src'] ?? null)<img src="{{ $photo['src'] }}" style="height:{{ $block['photo_height'] }}mm" alt="{{ $photo['caption'] }}">
                        @else<div class="empty-photo">Sin fotografía</div>@endif
                        <figcaption>{{ $photo['caption'] }}</figcaption>
                    </figure></td>
                @empty<td>—</td>@endforelse
                </tr></table>
            @else
                {{ $row[$column] ?? '—' }} @if($chunkIndex > 0)<p>(continuación)</p>@endif
            @endif
            </td>
        @endforeach
    </tr>
    @endforeach
@endforeach
</tbody>
</table>
