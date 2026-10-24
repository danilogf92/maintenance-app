<section class="planning-panel" aria-label="Planes del equipo">
 <header><h3>Planes de mantenimiento</h3><p>Programación e historial de visitas de este equipo.</p></header>
 @if($canManage)<a class="planning-button" href="{{ route('operations.maintenance_plans', ['new_for_equipment' => $equipmentId]) }}">Nuevo plan para este equipo</a>@endif
 @forelse($plans as $plan)<article class="planning-visit"><h4><a class="planning-link" href="{{ route('operations.maintenance_plans', ['record' => $plan->id]) }}">{{ $plan->code }} · {{ $plan->name }}</a></h4>
 <p class="calendar-origin">{{ $plan->contract_coverage_id ? 'Visitas por contrato' : 'Visitas independientes por equipo' }}</p>
 <p>{{ $plan->service_type }} · {{ $plan->visits_count }} visitas · {{ $plan->active ? 'Activo' : 'Inactivo' }}</p></article>
 @empty<p class="planning-empty">El equipo todavía no tiene planes de mantenimiento.</p>@endforelse
</section>
