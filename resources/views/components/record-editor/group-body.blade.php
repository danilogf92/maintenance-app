@props(['group', 'column', 'label', 'span', 'viewId'])
<tbody wire:key="table-group-{{ $viewId }}-{{ md5($column.'|'.$group['key']) }}"
 x-data="{expanded:false,grouped:@js($group['label'] !== null),ids:@js($group['items']->pluck('id')->all()),
 init(){this.$watch('$wire.editingId',()=>this.reveal());this.$watch('$wire.formOpen',()=>this.reveal())},
 reveal(){if(this.$wire.formOpen && this.ids.includes(Number(this.$wire.editingId))) this.expanded=true}}"
 @table-groups-toggle.window="expanded=$event.detail.open"
 @table-record-reveal="if(ids.includes(Number($event.detail.id))) expanded=true">
 @if($group['label'] !== null)
 <tr class="ops-group-heading"><td colspan="{{ $span }}">
  <button type="button" class="ops-group-toggle" @click="expanded=!expanded" :aria-expanded="expanded">
   <x-ui.icon name="chevron-right" class="ops-group-chevron" x-bind:class="{'is-open':expanded}" />
   <span class="ops-group-label">{{ $group['label'] }}</span>
   <span class="ops-group-count">{{ $group['items']->count() }} registros</span>
   <span class="ops-group-column">{{ $label }}</span>
  </button>
 </td></tr>
 @endif
 {{ $slot }}
</tbody>
