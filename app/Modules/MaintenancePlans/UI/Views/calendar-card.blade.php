@php([$tone,$label] = \App\Modules\MaintenancePlans\Configuration\CalendarStatus::forVisit($visit, $tickets->get($visit->id)))
@php($movable = $canManage && $plan->active && (!$plan->contract_coverage_id || $plan->coverage?->contract?->active) && !$visit->ticket_exists && $visit->status === 'planned')
<button type="button" class="calendar-card state-{{ $tone }} {{ $selectedVisitId === $visit->id ? 'is-selected' : '' }}" wire:key="calendar-visit-{{ $visit->id }}"
 wire:click="selectVisit({{ $visit->id }})" draggable="{{ $movable ? 'true' : 'false' }}" aria-pressed="{{ $selectedVisitId === $visit->id ? 'true' : 'false' }}"
 aria-label="{{ $plan->service_type }}, {{ $label }}, {{ $months[$index] }} {{ $year }}"
 @if($movable) @dragstart="if(busy){$event.preventDefault();return} drag={id:{{ $visit->id }},equipment:{{ $item->id }},version:{{ $visit->lock_version }}}; $event.dataTransfer.effectAllowed='move'; $event.dataTransfer.setData('text/plain','{{ $visit->id }}')" @endif>
 <small class="calendar-origin">{{ $plan->contract_coverage_id ? 'Contractual' : 'Independiente' }}</small>
 <strong>{{ $plan->service_type }}</strong><small>{{ $visit->technician?->name ?? 'Técnico por asignar' }}</small><small>{{ $visit->schedule_precision==='day' ? $visit->scheduled_for->format('d/m/Y') : 'Sin día definido' }}</small><span>{{ $label }}</span>
 @if(!$plan->active)<small>Plan inactivo</small>@endif
</button>
