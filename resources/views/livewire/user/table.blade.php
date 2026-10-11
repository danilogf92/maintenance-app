<div class="ops-table-wrap min-h-0 min-w-0 flex-1 overflow-auto" style="height:auto!important;min-height:0!important;flex:1 1 0%!important;" x-ref="records"><table class="ops-table"><thead><tr>
@foreach($visibleColumns as $column)
<x-record-editor.column-header :column="$column" :label="$availableColumns[$column] ?? $column"
 :definition="$tableColumns[$column]" :options="$columnOptions[$column] ?? []"
 :filter="$tableFilters[$column] ?? ''" :sort="$tableSort" :direction="$tableDirection" :group="$tableGroup" />
@endforeach
</tr></thead><tbody>
@foreach($tableGroups as $group)
@if($group['label'] !== null)
<tr class="ops-group-heading" wire:key="group-{{ md5($tableGroup.'|'.$group['key']) }}"><td colspan="{{ count($visibleColumns) }}">
{{ $availableColumns[$tableGroup] }}: {{ $group['label'] }} <span>({{ $group['items']->count() }})</span>
</td></tr>
@endif
@foreach($group['items'] as $item)
<tr wire:ignore.self wire:key="user-{{ $item->id }}" wire:click="edit({{ $item->id }})" class="ops-record-row cursor-pointer" data-record-id="{{ $item->id }}" :class="{'ops-record-selected': $wire.formOpen && Number($wire.editingId) === {{ $item->id }}}" :aria-selected="$wire.formOpen && Number($wire.editingId) === {{ $item->id }} ? 'true' : 'false'" tabindex="0" @keydown.enter="$wire.edit({{ $item->id }})">
@foreach($visibleColumns as $column)
<td wire:key="cell-{{ $item->id }}-{{ $column }}" data-label="{{ $availableColumns[$column] ?? $column }}">
@switch($column)
@case('name') <strong>{{ $item->name }}</strong><div class="ops-muted">{{ $item->email }}</div> @break
@case('institution') {{ $item->institution?->name ?? '—' }} @break
@case('job_title') {{ $item->job_title ?: '—' }} @break
@case('roles') {{ $item->roles->pluck('name')->join(', ') ?: '—' }} @break
@case('status') <span class="ops-status">{{ $item->is_active ? 'Activo' : 'Inactivo' }}</span> @break
@case('id') {{ $item->id }} @break
@case('email') {{ $item->email }} @break
@case('institution_id') {{ $item->institution_id ?? '—' }} @break
@case('identification') {{ $item->identification ?: '—' }} @break
@case('phone') {{ $item->phone ?: '—' }} @break
@case('can_access_admin') {{ $item->can_access_admin ? 'Sí' : 'No' }} @break
@case('email_verified_at') {{ $item->email_verified_at?->format('d/m/Y H:i') ?? '—' }} @break
@case('theme_preference') {{ $item->theme_preference ?: 'Sistema' }} @break
@case('created_at') {{ $item->created_at?->format('d/m/Y H:i') ?? '—' }} @break
@case('updated_at') {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }} @break
@endswitch
</td>
@endforeach

</tr>
@endforeach
@endforeach
@if($items->isEmpty())<tr><td colspan="{{ count($visibleColumns) }}" class="ops-empty">No se encontraron registros.</td></tr>@endif
</tbody></table><div class="ops-record-count">{{ $items->count() }} registro(s) en esta vista</div></div>
