@props(['id', 'label', 'icon' => 'archive', 'active' => false])
<details class="sidebar-group {{ $active ? 'is-current-group' : '' }}" @if($active) open @endif
 x-data="{expanded:@js((bool)$active),storageKey:@js('maintenance-nav-group-'.$id.'-'.auth()->id()),
 init(){try{const value=localStorage.getItem(this.storageKey);if(value!==null && !@js((bool)$active))this.expanded=value==='1';}catch(error){}},
 remember(){try{localStorage.setItem(this.storageKey,this.expanded?'1':'0');}catch(error){}}}"
 :open="expanded" @toggle="expanded=$el.open;remember()">
 <summary class="nav-item sidebar-group-toggle" @click.prevent="expanded=!expanded"
  :aria-expanded="expanded" aria-controls="{{ $id }}-items"
  :title="(expanded?'Contraer: ':'Expandir: ')+@js($label)">
  <span class="nav-icon"><x-ui.icon :name="$icon" class="h-5 w-5" /></span>
  <span class="sidebar-label sidebar-group-label" x-show="!collapsed || mobileOpen">{{ $label }}</span>
  <span class="sidebar-label sidebar-group-chevron" x-show="!collapsed || mobileOpen" aria-hidden="true"><x-ui.icon name="chevron-right" /></span>
 </summary>
 <div id="{{ $id }}-items" class="sidebar-group-items">{{ $slot }}</div>
</details>
