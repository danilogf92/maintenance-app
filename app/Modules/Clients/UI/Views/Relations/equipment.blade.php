<div>
 <h3 style="font-size:14px;font-weight:600">Equipos de este cliente</h3>
 <input class="record-related-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar equipo, código o serie…" aria-label="Buscar equipos relacionados">
 <div style="overflow:auto"><table class="record-related-table"><thead><tr><th>Código</th><th>Equipo</th><th>Serie</th><th>Institución</th><th>Estado</th></tr></thead>
 <tbody>@forelse($items as $equipment)
  <tr wire:key="client-equipment-{{ $equipment->id }}"><td>{{ $equipment->code }}</td>
   <td><a href="{{ route('operations.equipment', ['record' => $equipment->id]) }}"><x-ui.icon name="cpu" />{{ $equipment->name }}</a></td>
   <td>{{ $equipment->serial ?: '—' }}</td><td>{{ $equipment->institution?->name ?: '—' }}</td>
   <td>{{ \App\Modules\Equipment\Configuration\Fields::statuses()[$equipment->status] ?? $equipment->status }}</td></tr>
 @empty<tr><td colspan="5">No hay equipos vinculados a este cliente.</td></tr>@endforelse</tbody></table></div>
 {{ $items->links() }}
</div>
