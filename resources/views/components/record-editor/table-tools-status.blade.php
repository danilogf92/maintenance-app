@props(['columns', 'filters', 'sort', 'direction', 'group'])
@if(count($filters) || $sort !== '' || $group !== '')
<div class="ops-table-tools-status">
 @foreach($filters as $column => $value)
 <button type="button" wire:click="filterColumn('{{ $column }}', '')" title="Quitar filtro de {{ $columns[$column] }}">{{ $columns[$column] }}: filtro activo ×</button>
 @endforeach
 @if($sort !== '')<span>{{ $columns[$sort] }} {{ $direction === 'asc' ? '↑' : '↓' }}</span>@endif
 @if($group !== '')
 <button type="button" @click="$dispatch('table-groups-toggle',{open:true})">Expandir grupos</button>
 <button type="button" @click="$dispatch('table-groups-toggle',{open:false})">Colapsar grupos</button>
 <button type="button" wire:click="groupColumn('{{ $group }}')">Agrupado: {{ $columns[$group] }} ×</button>@endif
 <button type="button" wire:click="clearTableTools" class="ops-table-tools-reset">Restablecer tabla</button>
</div>
@endif
