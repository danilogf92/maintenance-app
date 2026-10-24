@if($planningContext)
<section class="record-editor-section"><h3>Mantenimiento programado</h3>
 <p>{{ $planningContext['name'] }}</p><p>Mes previsto: {{ $planningContext['month'] }}</p>
 <a class="planning-link" href="{{ route('operations.maintenance_plans', ['record' => $planningContext['id']]) }}">Abrir planificación</a>
</section>
@endif
