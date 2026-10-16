@php
    $formatTargets = ['table' => 'Encabezados de tabla', 'title' => 'Encabezado de columna', 'label' => $dataField ? 'Etiqueta del ítem' : 'Etiquetas de columna', 'value' => $dataField ? 'Valor del ítem' : 'Valores de columna'];
    if (($dataField['mode'] ?? '') === 'signature') { $formatTargets['signature'] = 'Firma del ítem'; }
    $target = array_key_exists($formatTarget, $formatTargets) ? $formatTarget : 'value';
    $formatKey = ['table' => 'table_header_style', 'title' => 'title_style', 'label' => 'label_style', 'value' => 'value_style', 'signature' => 'signature.style'][$target];
    $formatPath = $target === 'table' ? 'blockData.table_header_style' : ($target === 'title' || !$dataField ? $columnPath : $itemPath).'.'.$formatKey;
@endphp
<details class="rt-compact-settings rt-format-panel" open>
    <summary>Formato del elemento seleccionado</summary>
    <label>Aplicar formato a<select aria-label="Aplicar formato a" wire:model.live="formatTarget">@foreach($formatTargets as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
    <div wire:key="format-{{ $selected }}-{{ $dataColumnIndex }}-{{ $dataRowIndex }}-{{ $target }}">
        <label>Tamaño de letra (pt)<input type="number" min="6" max="72" step="0.5" list="rt-word-sizes" placeholder="Heredar" wire:model.live.debounce.600ms="{{ $formatPath }}.size"></label>
        <label>Color de letra (HEX)<input maxlength="7" placeholder="Heredar" wire:model.live.debounce.600ms="{{ $formatPath }}.color"></label>
        <label>Negrita<select aria-label="Negrita" wire:model.live="{{ $formatPath }}.bold"><option value="">Heredar</option><option value="1">Sí</option><option value="0">No</option></select></label>
        <details class="rt-compact-settings"><summary>Fuente y formato avanzado</summary>
            <label>Tipo de letra<select aria-label="Tipo de letra" wire:model.live="{{ $formatPath }}.font"><option value="">Heredar</option>@foreach(\App\Modules\ReportTemplates\Configuration\TextStyle::fonts() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
            <label>Cursiva<select aria-label="Cursiva" wire:model.live="{{ $formatPath }}.italic"><option value="">Heredar</option><option value="1">Sí</option><option value="0">No</option></select></label>
            <label>Alineación del texto<select aria-label="Alineación del texto" wire:model.live="{{ $formatPath }}.alignment"><option value="">Heredar</option><option value="left">Izquierda</option><option value="center">Centro</option><option value="right">Derecha</option></select></label>
            <label>Fondo (HEX)<input maxlength="7" placeholder="Heredar" wire:model.live.debounce.600ms="{{ $formatPath }}.background"></label>
        </details>
    </div>
    <p class="rt-help">Vacío = heredar. La alineación del ítem mueve etiqueta + valor juntos.</p>
</details>
