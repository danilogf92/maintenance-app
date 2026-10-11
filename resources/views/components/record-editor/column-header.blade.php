@props(['column', 'label', 'definition', 'options' => [], 'filter' => '', 'sort' => '', 'direction' => 'asc', 'group' => ''])
<th wire:key="head-{{ $column }}" aria-sort="{{ $sort === $column ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"
 x-data="{open:false,left:0,top:0,trigger:null,showMenu(event){
 this.trigger=event.currentTarget;
 const rect=this.trigger.getBoundingClientRect();
 this.left=Math.max(8,Math.min(rect.left,window.innerWidth-296));
 this.top=Math.max(8,Math.min(rect.bottom+6,window.innerHeight-320));
 this.open=!this.open;
 if(this.open) this.$nextTick(()=>this.$refs.columnMenu.querySelector('input,select,button')?.focus({preventScroll:true}));
 }}" @resize.window="open=false" @scroll.window="open=false" @keydown.escape.window="open=false">
 @if($definition['filterable'] || $definition['sortable'] || $definition['groupable'])
 <button type="button" class="ops-column-trigger" @click="showMenu($event)" :aria-expanded="open"
  aria-haspopup="dialog" title="Configurar {{ $label }}">
  <span>{{ $label }}</span>
  @if($sort === $column)<span aria-label="Orden {{ $direction === 'asc' ? 'ascendente' : 'descendente' }}">{{ $direction === 'asc' ? '↑' : '↓' }}</span>@endif
  @if($filter !== '')<span class="ops-column-mark" title="Filtro activo">●</span>@endif
  @if($group === $column)<span title="Agrupación activa">≡</span>@endif
  <span class="ops-column-chevron" aria-hidden="true">⌄</span>
 </button>
 <template x-teleport="body">
 <div x-ref="columnMenu" x-show="open" x-cloak class="ops-column-menu" role="dialog" aria-label="Columna {{ $label }}"
  :style="{left:left+'px',top:top+'px',maxHeight:Math.max(100,window.innerHeight-top-8)+'px'}" @click.outside="open=false" @keydown.escape.prevent.stop="open=false;trigger?.focus()">
  <header><strong>{{ $label }}</strong><button type="button" @click="open=false;trigger?.focus()" aria-label="Cerrar menú">×</button></header>
  @if($definition['filterable'])
  <label class="ops-column-filter">Filtrar esta columna
   @if($definition['kind'] === 'category')
   <select :value="$wire.tableFilters['{{ $column }}'] || ''"
    @change="$wire.filterColumn('{{ $column }}', $event.target.value)">
    <option value="">Todos</option>
    @foreach($options as $option)<option value="{{ $option['value'] }}">{{ $option['label'] }}</option>@endforeach
   </select>
   @else
   <input type="text" maxlength="200" :value="$wire.tableFilters['{{ $column }}'] || ''"
    placeholder="{{ $definition['kind'] === 'number' ? 'Valor exacto' : ($definition['kind'] === 'date' ? 'AAAA-MM-DD' : 'Contiene…') }}"
    @input.debounce.400ms="$wire.filterColumn('{{ $column }}', $event.target.value)">
   @endif
  </label>
  @if($filter !== '')<button type="button" class="ops-column-clear" @click="$wire.filterColumn('{{ $column }}', '')">Quitar filtro</button>@endif
  @endif
  <div class="ops-column-actions">
   @if($definition['sortable'])
   <button type="button" @click="$wire.sortColumn('{{ $column }}', 'asc'); open=false" aria-pressed="{{ $sort === $column && $direction === 'asc' ? 'true' : 'false' }}">↑ Ascendente</button>
   <button type="button" @click="$wire.sortColumn('{{ $column }}', 'desc'); open=false" aria-pressed="{{ $sort === $column && $direction === 'desc' ? 'true' : 'false' }}">↓ Descendente</button>
   @endif
   @if($definition['groupable'])
   <button type="button" @click="$wire.groupColumn('{{ $column }}'); open=false" aria-pressed="{{ $group === $column ? 'true' : 'false' }}">≡ {{ $group === $column ? 'Quitar agrupación' : 'Agrupar por '.$label }}</button>
   @endif
  </div>
 </div>
 </template>
 @else
 {{ $label }}
 @endif
</th>
