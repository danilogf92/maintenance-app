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
<tr x-show.important="!grouped || expanded" @if($group['label'] !== null) x-cloak @endif wire:ignore.self wire:key="client-{{ $item->id }}" wire:click="edit({{ $item->id }})" class="ops-record-row cursor-pointer" data-record-id="{{ $item->id }}" :class="{'ops-record-selected': $wire.formOpen && Number($wire.editingId) === {{ $item->id }}}" :aria-selected="$wire.formOpen && Number($wire.editingId) === {{ $item->id }} ? 'true' : 'false'" tabindex="0" @keydown.enter="$wire.edit({{ $item->id }})">
@foreach($visibleColumns as $column)
<td wire:key="cell-{{ $item->id }}-{{ $column }}" data-label="{{ $availableColumns[$column] ?? $column }}">
@switch($column)
@case('name') <strong>{{ $item->name }}</strong> @break
@case('code') {{ $item->code }} @break
@case('tax_id') {{ $item->tax_id ?: '—' }} @break
@case('status') <span class="ops-status">{{ $item->active ? 'Activo' : 'Inactivo' }}</span> @break
@case('city') {{ $item->city ?: '—' }} @break
@case('equipment') {{ $item->equipment_count ?? '—' }} @break
@case('id') {{ $item->id }} @break
@case('email') {{ $item->email ?: '—' }} @break
@case('phone') {{ $item->phone ?: '—' }} @break
@case('address') {{ $item->address ?: '—' }} @break
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
