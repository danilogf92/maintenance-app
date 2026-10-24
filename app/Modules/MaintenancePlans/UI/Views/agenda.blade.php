<div class="calendar-agenda" aria-label="Agenda de visitas">
 <p class="calendar-hint">{{ \Carbon\CarbonImmutable::parse($range[0])->format('d/m/Y') }} — {{ \Carbon\CarbonImmutable::parse($range[1])->format('d/m/Y') }}. Las visitas mensuales sin día definido aparecen como pendientes de concretar dentro del mes.</p>
 @forelse($agenda as $visit)
 @php([$tone,$label]=\App\Modules\MaintenancePlans\Configuration\CalendarStatus::forVisit($visit,$tickets->get($visit->id)))
 <article wire:key="agenda-{{ $visit->id }}"><button type="button" wire:click="selectVisit({{ $visit->id }})">
  <span class="calendar-origin">{{ $visit->plan->contract_coverage_id ? 'Contractual' : 'Independiente por equipo' }}</span>
  <strong>{{ $visit->plan->equipment->name }} · {{ $visit->plan->service_type }}</strong>
  <p>{{ \App\Modules\MaintenancePlans\Configuration\Periods::visitLabel($visit) }}{{ $visit->schedule_precision==='month' ? ' · Sin día definido' : '' }}</p>
  <p>{{ $visit->technician?->name ?? 'Técnico por asignar' }}</p><span class="calendar-state state-{{ $tone }}">{{ $label }}</span>
 </button></article>
 @empty<p class="calendar-empty">No hay visitas programadas para este período y estos filtros.</p>@endforelse
</div>
