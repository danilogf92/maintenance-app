<div class="ops-table-wrap min-h-0 min-w-0 flex-1 overflow-auto" style="height:auto!important;min-height:0!important;flex:1 1 0%!important;" x-ref="records"><table class="ops-table"><thead><tr>
@foreach($visibleColumns as $column)
<x-record-editor.column-header :column="$column" :label="$availableColumns[$column] ?? $column"
 :definition="$tableColumns[$column]" :options="$columnOptions[$column] ?? []"
 :filter="$tableFilters[$column] ?? ''" :sort="$tableSort" :direction="$tableDirection" :group="$tableGroup" />
@endforeach
</tr></thead>
@foreach($tableGroups as $group)
<x-record-editor.group-body :group="$group" :column="$tableGroup" :label="$availableColumns[$tableGroup] ?? ''" :span="count($visibleColumns)" :view-id="$currentViewId">
@foreach($group['items'] as $item)
<tr x-show.important="!grouped || expanded" @if($group['label'] !== null) x-cloak @endif wire:ignore.self wire:key="service-{{ $item->id }}" wire:click="edit({{ $item->id }})" class="ops-record-row cursor-pointer" data-record-id="{{ $item->id }}" :class="{'ops-record-selected': $wire.formOpen && Number($wire.editingId) === {{ $item->id }}}" :aria-selected="$wire.formOpen && Number($wire.editingId) === {{ $item->id }} ? 'true' : 'false'" tabindex="0" @keydown.enter="$wire.edit({{ $item->id }})">
@foreach($visibleColumns as $column)
<td wire:key="cell-{{ $item->id }}-{{ $column }}" data-label="{{ $availableColumns[$column] ?? $column }}">
@switch($column)
@case('service_type') <strong>{{ $item->service_type }}</strong> @break
@case('code') {{ $item->code }} @if($item->ninox_import_exists)<small class="record-editor-hint">Recibido</small>@endif @break
@case('status') {{ \App\Modules\Services\Configuration\Fields::statuses()[$item->status] ?? $item->status }} @break
@case('ticket') {{ $item->ticket?->code ?? '—' }} @break
@case('equipment') {{ $item->ticket?->equipment?->code ?? '—' }} @break
@case('technician') {{ $item->technician_label ?: '—' }} @break
@case('service_date') {{ $item->service_date?->format('d/m/Y') ?? '—' }} @break
@case('activities') {{ $item->activities_count ?? 0 }} @break
@case('reports') {{ $item->reports_count ?? 0 }} @break
@case('id') {{ $item->id }} @break
@case('created_at') {{ $item->created_at?->format('d/m/Y H:i') ?? '—' }} @break
@case('updated_at') {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }} @break
@endswitch
</td>
@endforeach

</tr>
@endforeach
</x-record-editor.group-body>
@endforeach
@if($items->isEmpty())<tbody><tr><td colspan="{{ count($visibleColumns) }}" class="ops-empty">No se encontraron registros.</td></tr></tbody>@endif
</table><div class="ops-record-count">{{ $items->count() }} registro(s) en esta vista</div></div>
