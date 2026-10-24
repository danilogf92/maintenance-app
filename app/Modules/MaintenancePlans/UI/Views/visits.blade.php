@php($planningActive=$plan->active && (!$plan->contract_coverage_id || $plan->coverage?->contract?->active))
<section class="planning-panel" aria-label="Visitas programadas">
 <header><h3>Visitas programadas</h3><p>{{ $plan->name }} · {{ $visits->count() }} visitas</p>
 @if($plan->interval_months || $plan->schedule_precision === 'day')<p>{{ \App\Modules\MaintenancePlans\Configuration\Schedule::label($plan) }} · Inicio: {{ \App\Modules\MaintenancePlans\Configuration\Periods::label($plan->first_month) }}</p>@endif</header>
 @if($notice)<p role="status" class="planning-notice">{{ $notice }}</p>@endif
 <x-record-editor.validation-summary />
 @if(!$plan->interval_months && $plan->schedule_precision === 'month')<p class="planning-notice">Plan histórico pendiente de revisión. Crea otro plan y confirma su frecuencia.</p>@endif
 @if($plan->contract_coverage_id && !$plan->coverage?->contract?->active)<p class="planning-notice">Contrato inactivo. Se conserva el historial; no se programan nuevas visitas.</p>@endif
 @if(!$plan->active)<p class="planning-notice">Plan inactivo. Se conserva su historial.</p>@endif
 @if($canManage && $planningActive && ($plan->interval_months || $plan->schedule_precision === 'day'))
 <form wire:submit="programVisits" class="planning-program">
  <label for="planning-count-{{ $planId }}">Cantidad de nuevas visitas<input id="planning-count-{{ $planId }}" type="number" min="1" max="120" wire:model="generationCount"></label>
  <button type="submit" class="planning-button" wire:loading.attr="disabled">Programar visitas</button>
  <p>Se añaden los siguientes períodos de la frecuencia configurada. Los tickets se generan por separado.</p>
 </form>
 @endif
 @if($editVisitId && $canManage) @include('maintenance-plans::visit-form') @endif
 <div class="planning-visits">
 @forelse($visits as $visit)
 @php($ticket = $tickets->get($visit->id))
 <article class="planning-visit {{ $editVisitId === $visit->id ? 'is-editing' : '' }}" wire:key="planned-visit-{{ $visit->id }}">
  <header><div><span>Visita {{ $visit->sequence }}</span><h4>{{ \App\Modules\MaintenancePlans\Configuration\Periods::visitLabel($visit) }}</h4></div>
   <span class="planning-badge">{{ \App\Modules\MaintenancePlans\Configuration\CalendarStatus::forVisit($visit,$ticket)[1] }}</span>
  </header>
  <p><x-ui.icon name="user" />{{ $visit->technician?->name ?? 'Técnico por asignar' }}</p>
  @if($visit->notes)<p>{{ $visit->notes }}</p>@endif
  <footer>
   @if($ticket)<a class="planning-link" href="{{ route('operations.tickets', ['record' => $ticket->id]) }}">{{ $ticket->code }} · {{ \App\Modules\Tickets\Configuration\Fields::statuses()[$ticket->status] ?? $ticket->status }}</a>
   @elseif($visit->ticket_exists)<span>Ticket asociado</span>
   @elseif($canManage)
    <button type="button" class="planning-secondary" wire:click="editVisit({{ $visit->id }})">Editar visita</button>
    @if($canGenerate && $visit->status === 'planned' && $planningActive)<button type="button" class="planning-button" wire:click="generateTicket({{ $visit->id }}, {{ $visit->lock_version }})" wire:loading.attr="disabled">Generar ticket</button>@endif
   @endif
  </footer>
 </article>
 @empty<p class="planning-empty">Todavía no hay visitas. Programa los primeros períodos desde este panel.</p>@endforelse
 </div>
 <p wire:loading.delay role="status">Procesando planificación…</p>
</section>
