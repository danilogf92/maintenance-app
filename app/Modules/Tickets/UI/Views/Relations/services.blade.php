<div>
 <h3 style="font-size:14px;font-weight:600">Servicios de este ticket</h3>
 @if(\App\Modules\Workflow\Configuration\Permissions::allows('services', 'manage') && !\App\Modules\Tickets\Models\Ticket::findOrFail($ticketId)->ninoxImport()->exists())
 <a href="{{ route('operations.services', ['new_for_ticket' => $ticketId]) }}"><x-ui.icon name="plus" />Nuevo servicio para este ticket</a>
 @endif
 <input class="record-related-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar código o tipo…" aria-label="Buscar servicios relacionados">
 <div style="overflow:auto"><table class="record-related-table"><thead><tr><th>Código</th><th>Tipo</th><th>Técnico</th><th>Estado</th><th>Fecha</th></tr></thead>
 <tbody>@forelse($items as $service)
  <tr wire:key="ticket-service-{{ $service->id }}"><td><a href="{{ route('operations.services', ['record' => $service->id]) }}"><x-ui.icon name="wrench" />{{ $service->code }}</a></td>
   <td>{{ $service->service_type }}</td><td>{{ $service->technician?->name ?: '—' }}</td>
   <td>{{ \App\Modules\Services\Configuration\Fields::statuses()[$service->status] ?? $service->status }}</td>
   <td>{{ $service->service_date?->format('d/m/Y') }}</td></tr>
 @empty<tr><td colspan="5">No hay servicios visibles para este ticket.</td></tr>@endforelse</tbody></table></div>
 {{ $items->links() }}
</div>
