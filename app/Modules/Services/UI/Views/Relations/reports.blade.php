<div>
 <h3 style="font-size:14px;font-weight:600">Informes de este servicio</h3>
 @if(\App\Modules\Workflow\Configuration\Permissions::allows('technical_reports', 'manage') && !\App\Modules\Services\Models\Service::findOrFail($serviceId)->ninoxImport()->exists())
 <p><a class="ops-button" href="{{ route('operations.technical_reports', ['new_for_service' => $serviceId]) }}"><x-ui.icon name="plus" />Nuevo informe de este servicio</a></p>
 @endif
 <input class="record-related-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar código o título…" aria-label="Buscar informes relacionados">
 <div style="overflow:auto"><table class="record-related-table"><thead><tr><th>Código</th><th>Título</th><th>Revisión</th><th>Estado</th><th>Creación</th></tr></thead>
 <tbody>@forelse($items as $report)
  <tr wire:key="service-report-{{ $report->id }}"><td><a href="{{ route('operations.technical_reports', ['record' => $report->id]) }}"><x-ui.icon name="file-text" />{{ $report->code }}</a></td>
   <td>{{ $report->title }}</td><td>{{ $report->revision }}</td><td>Borrador</td><td>{{ $report->created_at?->format('d/m/Y') }}</td></tr>
 @empty<tr><td colspan="5">No hay informes visibles para este servicio.</td></tr>@endforelse</tbody></table></div>
 <p class="record-editor-hint" style="margin-top:12px">Abre el informe para revisar sus datos, generar el PDF por cola y consultar su vista previa.</p>

</div>
