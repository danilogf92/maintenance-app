<div>
 <h3 style="font-size:14px;font-weight:600">Actividades de este servicio</h3>
 @if(\App\Modules\Workflow\Configuration\Permissions::allows('activities', 'manage'))
 <a href="{{ route('operations.activities', ['new_for_service' => $serviceId]) }}"><x-ui.icon name="plus" />Nueva actividad para este servicio</a>
 @endif
 <input class="record-related-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar código o descripción…" aria-label="Buscar actividades relacionadas">
 <div style="overflow:auto"><table class="record-related-table"><thead><tr><th>Código</th><th>Actividad / hallazgo</th><th>Resultado</th><th>Fotografías</th></tr></thead>
 <tbody>@forelse($items as $activity)
  <tr wire:key="service-activity-{{ $activity->id }}"><td><a href="{{ route('operations.activities', ['record' => $activity->id]) }}"><x-ui.icon name="activity" />{{ $activity->code }}</a></td>
   <td>{{ \Illuminate\Support\Str::limit($activity->description, 180) }}</td><td>{{ \Illuminate\Support\Str::limit($activity->result ?: 'Sin resultado registrado', 180) }}</td>
   <td>{{ count($activity->photos ?? []) }}</td></tr>
 @empty<tr><td colspan="4">No hay actividades visibles para este servicio.</td></tr>@endforelse</tbody></table></div>
 <p class="record-editor-hint" style="margin-top:12px">Abre una actividad para consultar sus fotografías privadas y sus datos completos.</p>

</div>
