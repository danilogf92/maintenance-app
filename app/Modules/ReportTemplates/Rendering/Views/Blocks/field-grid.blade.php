<table class="fields">
@foreach(array_chunk($block['fields'], (int)$block['columns']) as $fieldRow)
<tr>
@foreach($fieldRow as $key)
<td><span class="field-label">{{ \App\Modules\ReportTemplates\Configuration\Variables::options()[$key] }}</span>
{{ \App\Modules\ReportTemplates\Configuration\Variables::value($data, $key) }}</td>
@endforeach
@for($empty = count($fieldRow); $empty < (int)$block['columns']; $empty++)<td></td>@endfor
</tr>
@endforeach
</table>
