<div class="calendar-backdrop" wire:click.self="closeVisit">
 <aside class="calendar-detail" role="dialog" aria-modal="true" aria-labelledby="calendar-detail-title" x-data="{}" x-trap.inert.noscroll="true">
 <header><div><span class="calendar-eyebrow">VISITA {{ $selected->sequence }}</span><h2 id="calendar-detail-title">{{ $selected->plan->service_type }}</h2></div><button type="button" wire:click="closeVisit" aria-label="Cerrar visita">×</button></header>
 <p><strong>{{ $selected->plan->equipment->name }}</strong></p><p>{{ \App\Modules\MaintenancePlans\Configuration\Periods::visitLabel($selected) }}</p>
 <p>{{ $selected->plan->contract_coverage_id ? 'Visita contractual' : 'Visita independiente por equipo' }}</p>
 @if($selected->plan->coverage && \App\Modules\Contracts\Configuration\Permissions::allows())<a class="calendar-ticket" href="{{ route('operations.contracts',['record'=>$selected->plan->coverage->maintenance_contract_id]) }}">Contrato {{ $selected->plan->coverage->contract->number }}</a>@endif
 @php([$tone,$label] = \App\Modules\MaintenancePlans\Configuration\CalendarStatus::forVisit($selected,$ticket))
 <span class="calendar-state state-{{ $tone }}">{{ $label }}</span>
 @include('maintenance-plans::calendar-validation')
 @if($notice)<p role="status" class="calendar-notice">{{ $notice }}</p>@endif
 @if($editVisitId)<div class="planning-panel"> @include('maintenance-plans::visit-form')</div>
 @else<p>Técnico: {{ $selected->technician?->name ?? 'Por asignar' }}</p>@if($selected->notes)<p class="calendar-notes">{{ $selected->notes }}</p>@endif
 @if($ticket)<a class="calendar-ticket" href="{{ route('operations.tickets',['record'=>$ticket->id]) }}">Abrir ticket {{ $ticket->code }}</a>
 @elseif($selected->ticket_exists)<p>Esta visita ya tiene un ticket asociado.</p>@endif
 @if(!$selected->plan->active)<p>Plan inactivo. Se conserva el historial.</p>@endif
 @endif
 <footer><a href="{{ route('operations.maintenance_plans',['record'=>$planId]) }}">Abrir plan {{ $selected->plan->code }}</a><button type="button" wire:click="closeVisit">Cerrar</button></footer>
 </aside>
</div>
