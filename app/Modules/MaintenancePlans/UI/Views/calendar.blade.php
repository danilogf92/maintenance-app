<section class="plan-calendar" aria-label="Calendario de planificación" x-data="{drag:null,over:null,busy:false,dragError:''}"
 @dragend.window="drag=null; over=null" @keydown.escape.window="$wire.closeVisit()">
 <header class="calendar-heading"><div><span class="calendar-eyebrow">PLANIFICACIÓN DE EQUIPOS</span><h1>Calendario de mantenimiento</h1><p>Visitas contractuales e independientes · Cumplimiento al completar el servicio</p></div>
 <div class="calendar-actions"><a href="{{ route('operations.maintenance_plans') }}"><x-ui.icon name="list" />Ver planes</a>
 @if($canManage)<a class="calendar-primary" href="{{ route('operations.maintenance_plans', ['new' => 1]) }}"><x-ui.icon name="plus" />Nuevo plan</a>@endif</div></header>
 <div class="calendar-controls"><div class="calendar-year"><button wire:click="changeYear(-1)" aria-label="Año anterior" @disabled($year <= 1900)>‹</button><strong>{{ $year }}</strong><button wire:click="changeYear(1)" aria-label="Año siguiente" @disabled($year >= 2200)>›</button><button wire:click="currentYear">Año actual</button></div>
 <label class="calendar-search"><x-ui.icon name="search" /><input type="search" placeholder="Buscar equipo, código o serie" aria-label="Buscar equipo, código o serie" wire:model.live.debounce.350ms="search"></label>
 <label class="calendar-mode-label">Origen<select wire:model.live="origin"><option value="all">Todas las visitas</option><option value="contract">Por contrato</option><option value="equipment">Independientes por equipo</option></select></label>
 @if($contractOptions)<label class="calendar-mode-label">Contrato<select wire:model.live="contractId"><option value="">Todos los contratos</option>@foreach($contractOptions as $id=>$number)<option value="{{ $id }}">{{ $number }}</option>@endforeach</select></label>@endif
 <label class="calendar-mode-label">Vista<select wire:model.live="viewMode"><option value="year">Año · Matriz mensual</option><option value="month">Mes · Agenda</option><option value="week">Semana · Agenda</option><option value="day">Día · Agenda</option></select></label>
 @if($viewMode!=='year')<label class="calendar-mode-label">Fecha de referencia<input type="date" wire:model.live="anchor"></label>@endif
 <span>{{ $equipment->count() }} equipos · {{ $totalVisits }} visitas</span></div>
 <div class="calendar-legend" aria-label="Estados de visita">@foreach(['planned'=>'Programada','overdue'=>'Pendiente vencida','ticket'=>'Con ticket','progress'=>'En atención','resolved'=>'Ticket resuelto','completed'=>'Servicio completado','cancelled'=>'Cancelada'] as $tone=>$label)<span class="calendar-state state-{{ $tone }}">{{ $label }}</span>@endforeach</div>
 @if($notice && !$selected)<p class="calendar-notice" role="status">{{ $notice }}</p>@endif
 @unless($selected)@include('maintenance-plans::calendar-validation')@endunless
 <p role="alert" class="calendar-error" x-show="dragError" x-text="dragError" x-cloak></p>
 <p wire:loading.delay role="status" class="calendar-notice">Actualizando calendario…</p>
 @if($equipment->isEmpty())
 <div class="calendar-empty"><x-ui.icon name="calendar" /><h2>No hay equipos para mostrar</h2><p>{{ $search !== '' ? 'Prueba otra búsqueda.' : 'Carga los equipos reales y crea sus planes para visualizar las visitas mensuales.' }}</p>
 @if(\App\Modules\DataReview\Queries\ReviewStatus::allowed())<a href="{{ route('operations.data-review') }}">Ver datos por completar</a>@endif</div>
 @elseif($viewMode!=='year')
 @include('maintenance-plans::agenda')
 @else
 <p class="calendar-hint">Pulsa una visita para ver sus detalles.@if($canManage) Arrastra a otro mes de la misma fila para reprogramar, o edita el período desde la ficha. En visitas con fecha exacta se conserva el día cuando existe en el mes de destino.@endif Los meses vacíos no generan visitas automáticamente.</p>
 <div class="calendar-scroll" tabindex="0" aria-label="Calendario anual, desplaza horizontalmente para ver todos los meses" wire:loading.class="calendar-pending">
 <table class="calendar-table"><caption class="sr-only">Planificación mensual de equipos para {{ $year }}</caption><thead><tr><th scope="col">Equipo / institución</th>
 @foreach($months as $index=>$month)<th scope="col" class="{{ now()->format('Y-m') === sprintf('%d-%02d',$year,$index+1) ? 'current-month' : '' }}">{{ $month }}</th>@endforeach</tr></thead><tbody>
 @foreach($equipment as $item)
 @php($plans = $plansByEquipment->get($item->id, collect()))
 <tr wire:key="calendar-equipment-{{ $item->id }}"><th scope="row"><a href="{{ route('operations.equipment', ['record'=>$item->id]) }}">{{ $item->name }}</a><small>{{ $item->code }} · {{ $item->serial ?: 'Sin serie' }}</small><small>{{ $item->institution?->name }}</small>
 @if($plans->isEmpty())<span class="calendar-unplanned">Sin plan</span>@if($canManage)<a class="calendar-plan-link" href="{{ route('operations.maintenance_plans',['new_for_equipment'=>$item->id]) }}">Crear plan</a>@endif@elseif($plans->sum(fn($p)=>$p->visits->count())===0)<span class="calendar-unplanned">Sin visitas este año</span>@endif</th>
 @foreach($months as $index=>$month)
 @php($period = sprintf('%d-%02d',$year,$index+1))
 <td :class="{'calendar-drop':over==='{{ $item->id }}-{{ $period }}'}"
 @dragover.prevent="if(drag && !busy && drag.equipment==={{ $item->id }}) over='{{ $item->id }}-{{ $period }}'"
 @dragleave="over=null"
 @drop.prevent="if(drag && !busy && drag.equipment==={{ $item->id }}){const moved=drag; drag=null; over=null; busy=true; dragError=''; $wire.moveVisit(moved.id,'{{ $period }}',moved.version).catch(()=>dragError='No se pudo mover la visita. Revisa el mensaje de validación o vuelve a abrir el calendario.').finally(()=>busy=false)}">
 @foreach($plans as $plan) @foreach($plan->visits->where('scheduled_month',$period) as $visit)
 @include('maintenance-plans::calendar-card')
 @endforeach @endforeach
 </td>@endforeach</tr>@endforeach
 </tbody></table></div>
 @endif
 @if($selected) @include('maintenance-plans::calendar-detail') @endif
</section>
