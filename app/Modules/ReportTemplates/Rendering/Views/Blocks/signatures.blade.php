@include('report-print::Blocks.title')
<table class="signatures"><tr>
<td><div class="signature-line">{{ data_get($data, 'technician.name', 'Técnico') }}<br>Responsable técnico</div></td>
<td><div class="signature-line">Responsable de la institución<br>{{ data_get($data, 'institution.name', '') }}</div></td>
</tr></table>
