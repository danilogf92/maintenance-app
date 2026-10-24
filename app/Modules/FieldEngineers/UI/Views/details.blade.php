@if($engineerDetails)
<p class="record-editor-hint">Ingeniero recibido · Referencia {{ $engineerDetails['reference'] }}. Se conserva como persona de servicio, sin crear ni vincular una cuenta de acceso.</p>
<p class="record-editor-hint">No se ha supuesto su estado laboral ni se han generado visitas de calendario. No se recibieron fotografías.</p>
@if($engineerDetails['private'])<details class="record-editor-section"><summary>Identificación y valores registrados</summary><x-record-editor.source-details>
@foreach(['id_number'=>'Identificación','cost_per_hour'=>'Costo por hora registrado','bonus_percentage'=>'Bono registrado (valor original)'] as $key=>$label)<div><dt>{{ $label }}</dt><dd>{{ $engineerDetails['private'][$key] ?? '—' }}</dd></div>@endforeach
</x-record-editor.source-details></details>@endif
@foreach(['visits'=>'Referencias de asignaciones / visitas','tasks'=>'Tareas','certifications'=>'Certificaciones','contract_schedule'=>'Cronogramas de contrato'] as $key=>$label)
<details class="record-editor-section"><summary>{{ $label }} · {{ count($engineerDetails['references'][$key] ?? []) }}</summary><p style="overflow-wrap:anywhere">{{ implode(', ',$engineerDetails['references'][$key] ?? []) ?: 'Sin referencias' }}</p><p class="record-editor-hint">Son referencias originales; no representan visitas mensuales creadas ni certificaciones importadas.</p></details>
@endforeach
@endif
