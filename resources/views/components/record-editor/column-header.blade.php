@props([
    'tag' => 'th',
    'column',
    'label',
    'definition',
    'options' => [],
    'filter' => '',
    'sort' => '',
    'direction' => 'asc',
    'group' => '',
])
<{{ $tag }} wire:key="head-{{ $tag }}-{{ $column }}"
    aria-sort="{{ $sort === $column ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"
    x-data="{
        open: false,
        draft: '',
        query: '',
        trigger: null,
        showMenu(event) {
            this.trigger = event.currentTarget;
            this.draft = this.$wire.tableFilters[@js($column)] || '';
            this.query = '';
            this.open = true;
            this.$nextTick(() => { if (!this.$refs.columnMenu.open) this.$refs.columnMenu.showModal() })
        },
        closeMenu() {
            this.open = false;
            this.$refs.columnMenu.close();
            this.trigger?.focus()
        },
        async applyFilter() {
            await this.$wire.filterColumn(@js($column), this.draft);
            this.closeMenu()
        },
        destroy() { this.$refs.columnMenu?.close() }
    }">
    @if ($definition['filterable'] || $definition['sortable'] || $definition['groupable'])
        <button type="button" class="ops-column-trigger" @click="showMenu($event)" :aria-expanded="open"
            aria-haspopup="dialog" title="Opciones de {{ $label }}">
            <span>{{ $label }}</span>
            @if ($sort === $column)
                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
            @endif
            @if ($filter !== '')<x-ui.icon name="filter"
                    class="ops-column-mark" />@endif
            @if ($group === $column)<x-ui.icon name="table"
                    class="ops-column-mark" />@endif
            <x-ui.icon name="chevron-down" class="ops-column-chevron" />
        </button>
        <template x-teleport="body">
            <dialog x-ref="columnMenu" wire:ignore.self class="ops-column-menu"
                aria-label="Opciones de {{ $label }}"
                @cancel.prevent="closeMenu()" @keydown.escape.stop
  @click="if($event.target===$refs.columnMenu)closeMenu()">
  <div class="ops-column-card">
   <header><div><small>Opciones de columna</small><strong>{{ $label }}</strong></div>
    <button type="button" @click="closeMenu()" aria-label="Cerrar opciones"><x-ui.icon name="x" /></button></header>
   @if ($definition['sortable'])
   <div class="ops-column-order" aria-label="Ordenar">
    <button type="button" @click="await $wire.sortColumn('{{ $column }}','asc');closeMenu()" aria-pressed="{{ $sort === $column && $direction === 'asc' ? 'true' : 'false' }}"><x-ui.icon name="arrow-up" />Ascendente</button>
    <button type="button" @click="await $wire.sortColumn('{{ $column }}','desc');closeMenu()" aria-pressed="{{ $sort === $column && $direction === 'desc' ? 'true' : 'false' }}"><x-ui.icon name="arrow-down" />Descendente</button>
   </div>
   @endif
   @if ($definition['filterable'])
   <div class="ops-column-filter">
    <label for="filter-{{ $tag }}-{{ $column }}">{{ $definition['kind'] === 'category' ? 'Buscar una opción' : 'Filtrar registros' }}</label>
    @if ($definition['kind'] === 'category')
    <input id="filter-{{ $tag }}-{{ $column }}" type="search" x-model="query" placeholder="Buscar…" autocomplete="off">
    <div class="ops-column-options" role="group" aria-label="Valores de {{ $label }}">
     <button type="button" @click="draft=''" :aria-pressed="draft===''">Todos los valores <span x-show="draft===''">✓</span></button>
     @foreach ($options as $option)
     <button type="button" x-show="@js(mb_strtolower($option['label'])).includes(query.toLocaleLowerCase())"
      @click="draft=@js($option['value'])" :aria-pressed="draft===@js($option['value'])">
      {{ $option['label'] }}<span x-show="draft===@js($option['value'])">✓</span>
     </button>
     @endforeach
    </div>
    @else
    <input id="filter-{{ $tag }}-{{ $column }}" type="{{ $definition['kind'] === 'date' ? 'date' : ($definition['kind'] === 'number' ? 'number' : 'text') }}"
     maxlength="200" x-model="draft" placeholder="{{ $definition['kind'] === 'number' ? 'Valor exacto' : 'Contiene…' }}" @keydown.enter.prevent="applyFilter()">
    <small>{{ $definition['kind'] === 'date' ? 'Registros de la fecha seleccionada.' : ($definition['kind'] === 'number' ? 'Coincidencia exacta.' : 'Busca este texto dentro del valor.') }}</small>
    @endif
   </div>
   <footer class="ops-column-footer">
    <button type="button" @click="draft='';await applyFilter()" class="ops-column-clear">Limpiar filtro</button>
    <button type="button" @click="applyFilter()" class="ops-column-apply" wire:loading.attr="disabled">Aplicar filtro</button>
   </footer>
   @endif
   @if ($definition['groupable'])
   <button type="button" class="ops-column-group" @click="await $wire.groupColumn('{{ $column }}');closeMenu()">
    <x-ui.icon name="table" />{{ $group === $column ? 'Quitar agrupación' : 'Agrupar por ' . $label }}
   </button>
   @endif
  </div>
 </dialog>
 </template>
 @else
 {{ $label }} @endif
                </{{ $tag }}>
