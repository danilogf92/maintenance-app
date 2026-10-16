@php
    $sections = array_values($blockData['data_sections']);
    $dataSection = $sections[$dataColumnIndex];
    $columnPath = $this->dataColumnPath();
    $dataFields = array_values($dataSection['fields']);
    $dataField = $dataRowIndex !== null ? ($dataFields[$dataRowIndex] ?? null) : null;
    $itemPath = $dataField ? $this->dataItemPath() : null;
@endphp
<fieldset class="rt-data-editor" wire:loading.attr="disabled" wire:target="selectDataItem,addDataItem,removeDataItem,moveDataItem,addDataColumn,removeDataColumn,formatTarget">
    <nav class="rt-data-columns" aria-label="Elegir columna">
        @foreach($sections as $index => $section)
            <button type="button" class="{{ $index === $dataColumnIndex ? 'is-selected' : '' }}" wire:click="selectDataItem({{ $selected }}, {{ $index }})">{{ $section['title'] }}</button>
        @endforeach
        <button type="button" wire:click="addDataColumn" @disabled(count($sections) >= 3)>+ Columna</button>
    </nav>
    <details class="rt-compact-settings"><summary>Tabla: títulos, colores y bordes</summary>
        <label class="rt-check"><input type="checkbox" wire:model.live="blockData.show_table_header"> Mostrar encabezados</label>
        <label class="rt-check"><input type="checkbox" wire:model.live="blockData.show_labels"> Mostrar etiquetas</label>
        <label class="rt-check"><input type="checkbox" wire:model.live="blockData.bordered"> Mostrar bordes</label>
        <label class="rt-check"><input type="checkbox" wire:model.live="blockData.repeat_header"> Repetir encabezados</label>
        @foreach(['section_background' => 'Fondo de títulos', 'section_color' => 'Letra de títulos', 'label_color' => 'Letra de etiquetas', 'value_color' => 'Letra de valores'] as $key => $label)
            <label>{{ $label }} (HEX)<input maxlength="7" placeholder="#00858B" wire:model.live.debounce.600ms="blockData.{{ $key }}"></label>
        @endforeach
    </details>
    <details class="rt-compact-settings"><summary>Columna: título y alineación</summary>
        <label>Título de columna<input maxlength="80" wire:model.live.debounce.600ms="{{ $columnPath }}.title"></label>
        <label class="rt-check"><input type="checkbox" wire:model.live="{{ $columnPath }}.show_title"> Mostrar este título</label>
        <label>Alineación de los ítems de esta columna<select aria-label="Alineación de los ítems de esta columna" wire:model.live="{{ $columnPath }}.item_alignment">
            <option value="left">Izquierda</option><option value="center">Centro</option><option value="right">Derecha</option><option value="legacy">Diseño anterior en celdas</option>
        </select></label>
        <button type="button" wire:click="removeDataColumn" wire:confirm="¿Quitar esta columna y sus ítems?" @disabled(count($sections) <= 1)>Quitar columna</button>
    </details>
    <div class="rt-item-list" aria-label="Ítems de la columna">
        @foreach($dataFields as $index => $field)
            <button type="button" class="{{ $dataRowIndex === $index ? 'is-selected' : '' }}" wire:click="selectDataItem({{ $selected }}, {{ $dataColumnIndex }}, {{ $index }})">
                <span>{{ ($field['label'] ?? '') ?: 'Ítem '.($index + 1) }}</span><small>{{ ['variable' => 'Variable', 'manual' => 'Texto', 'signature' => 'Firma'][$field['mode']] }}</small>
            </button>
        @endforeach
    </div>
    <div class="rt-add-item">
        <label>Nuevo ítem<select aria-label="Nuevo ítem" wire:model="newDataFieldKind"><option value="variable">Variable</option><option value="manual">Texto</option><option value="signature">Firma individual</option></select></label>
        <button type="button" wire:click="addDataItem" @disabled(count($dataFields) >= 30)>Añadir ítem</button>
    </div>
    @if($dataField)
        @include('report-templates::Partials.DataEditor.item')
    @else
        <p class="rt-help">Selecciona un ítem de la lista o del informe para editarlo. Ahora estás configurando la columna.</p>
    @endif
    @include('report-templates::Partials.DataEditor.format')
</fieldset>
