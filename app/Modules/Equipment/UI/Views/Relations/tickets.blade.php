<div>
 <h3 style="font-size:14px;font-weight:600">Tickets de este equipo</h3>
 @if(\App\Modules\Workflow\Configuration\Permissions::allows('tickets', 'manage'))
 <a href="{{ route('operations.tickets', ['new_for_equipment' => $equipmentId]) }}"><x-ui.icon name="plus" />Nuevo ticket para este equipo</a>
 @endif
 <input class="record-related-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar código o asunto…" aria-label="Buscar tickets relacionados">
 <div style="overflow:auto"><table class="record-related-table"><thead><tr><th>Código</th><th>Asunto</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th>Apertura</th></tr></thead>
 <tbody>@forelse($items as $ticket)
  <tr wire:key="equipment-ticket-{{ $ticket->id }}"><td><a href="{{ route('operations.tickets', ['record' => $ticket->id]) }}"><x-ui.icon name="ticket" />{{ $ticket->code }}</a></td>
   <td>{{ $ticket->subject }}</td><td>{{ $ticket->technician?->name ?: '—' }}</td>
   <td>{{ \App\Modules\Tickets\Configuration\Fields::statuses()[$ticket->status] ?? $ticket->status }}</td>
   <td>{{ ['low'=>'Baja','normal'=>'Normal','high'=>'Alta','urgent'=>'Urgente'][$ticket->priority] ?? $ticket->priority }}</td>
   <td>{{ $ticket->opened_at?->format('d/m/Y H:i') }}</td></tr>
 @empty<tr><td colspan="6">No hay tickets visibles para este equipo.</td></tr>@endforelse</tbody></table></div>
 {{ $items->links() }}
</div>
