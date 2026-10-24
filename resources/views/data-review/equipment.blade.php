<h2 class="my-4 text-lg font-semibold">Equipos y tickets</h2>
<p>{{ $equipmentReview['count'] }} equipos recibidos · {{ $equipmentReview['linkedTickets'] }} tickets vinculados a equipos.</p>
<p>{{ $missingEquipment }} tickets con referencia siguen pendientes de vincular; {{ $withoutEquipmentReference }} no traen referencia de equipo y se conservan sin asignar.</p>
<p>Equipos con cliente pendiente: {{ $equipmentReview['histories']['clients'] }}. Con institución pendiente: {{ $equipmentReview['histories']['institutions'] }}.</p>
<p>{{ $equipmentReview['pendingTickets'] }} referencias de tickets aparecen en los equipos y faltan en el archivo de Tickets recibido. Se necesita una exportación actualizada; no se crean tickets vacíos.</p>
@if($equipmentReview['pendingTickets'])<details class="my-4"><summary>Ver referencias de tickets faltantes</summary><p style="overflow-wrap:anywhere">{{ implode(', ',$equipmentReview['pendingTicketReferences']) }}</p></details>@endif
@foreach(['missingModels'=>'Modelos faltantes','missingBrands'=>'Marcas faltantes'] as $key=>$label)
@foreach($equipmentReview[$key] as $reference=>$records)
<details class="my-4"><summary>{{ $label }} · Referencia {{ $reference }} · {{ count($records) }} equipos</summary><ul>
@foreach($records as $record)<li><a href="{{ route('operations.equipment',['record'=>$record['id']]) }}">Serie {{ $record['serial'] }}</a></li>@endforeach
</ul></details>
@endforeach @endforeach
@foreach($equipmentReview['conflicts'] as $conflict)
<p role="status">Ticket <a href="{{ route('operations.tickets',['record'=>$conflict['id']]) }}">{{ $conflict['code'] }}</a> (referencia {{ $conflict['reference'] }}): apunta al equipo {{ $conflict['equipment'] }}; las listas de equipos lo reclaman en {{ implode(', ',$conflict['claims']) }}. No se reasigna hasta resolver la contradicción.</p>
@endforeach

@if($equipmentReview['catalogEquipmentPending'])<details class="my-4"><summary>Referencias de equipos en catálogos anteriores que no llegaron en este archivo · {{ count($equipmentReview['catalogEquipmentPending']) }}</summary><p>{{ implode(', ',$equipmentReview['catalogEquipmentPending']) }}</p><p>Verificar si faltan en la exportación o corresponden a registros anteriores. Se conservan las referencias originales.</p></details>@endif
