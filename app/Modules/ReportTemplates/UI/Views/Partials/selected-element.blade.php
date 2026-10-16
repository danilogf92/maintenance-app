@php
    $lineKey = $this->selectedLineKey();
    $columnLabel = ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'][$selectedColumn ?? ''] ?? '';
    $typeLabel = \App\Modules\ReportTemplates\Configuration\Blocks::options()[$blockData['type'] ?? 'header'] ?? 'Bloque';
@endphp
<div class="rt-selection" role="status" aria-live="polite">
    <strong>Editando: {{ $typeLabel }}@if($columnLabel) · {{ $columnLabel }}@endif @if($lineKey !== null) · Línea {{ $selectedLine + 1 }}@endif</strong>
    @if($lineKey !== null)
        @php($path = 'blockData.header_columns.'.$selectedColumn.'.lines.'.$lineKey)
        <div class="rt-quick-fields" wire:key="quick-{{ $selected }}-{{ $selectedColumn }}-{{ $lineKey }}">
            <label>Texto / texto antes del dato<input maxlength="160" wire:model.live.debounce.600ms="{{ $path }}.text"></label>
            <label>Contenido<select wire:model.live="{{ $path }}.mode"><option value="manual">Texto manual</option><option value="variable">Texto + dato automático</option></select></label>
            @if(($blockData['header_columns'][$selectedColumn]['lines'][$lineKey]['mode'] ?? '') === 'variable')
            <label>Dato automático<select wire:model.live="{{ $path }}.variable">@foreach(\App\Modules\ReportTemplates\Configuration\Variables::options() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
            @endif
            <label>Tamaño de letra (pt)<input type="number" min="6" max="72" step="0.5" list="rt-word-sizes" wire:model.live.debounce.600ms="{{ $path }}.size"></label>
            <label>Color de letra (HEX)<input type="text" maxlength="7" pattern="#[0-9A-Fa-f]{6}" placeholder="#00858B" wire:model.live.debounce.600ms="{{ $path }}.color"></label>
            <label class="rt-check"><input type="checkbox" wire:model.live="{{ $path }}.bold"> Negrita</label>
        </div>
    @elseif($selectedColumn !== null)
        <div class="rt-quick-fields">
            <label>Altura máxima del logo (mm)<input type="number" min="8" max="40" wire:model.live.debounce.600ms="blockData.header_columns.{{ $selectedColumn }}.logo_height"></label>
            <p class="rt-help">En las propiedades de esta columna puedes cambiar el origen del logo, borde y alineación.</p>
        </div>
    @elseif(($blockData['type'] ?? '') !== 'header')
        <div class="rt-quick-fields">
            <label>Título del bloque<input maxlength="120" wire:model.live.debounce.600ms="blockData.title"></label>
            <label>Tamaño de letra del bloque (pt)<input type="number" min="6" max="72" step="0.5" list="rt-word-sizes" placeholder="Heredar del papel" wire:model.live.debounce.600ms="blockData.font_size"></label>
            @if(($blockData['type'] ?? '') === 'separator')
            <label>Fondo del separador (HEX)<input type="text" maxlength="7" pattern="#[0-9A-Fa-f]{6}" placeholder="#00858B" wire:model.live.debounce.600ms="blockData.separator_background"></label>
            <label>Color de letra (HEX)<input type="text" maxlength="7" pattern="#[0-9A-Fa-f]{6}" placeholder="#00858B" wire:model.live.debounce.600ms="blockData.separator_color"></label>
            @endif
        </div>
    @else
        <p class="rt-help">Haz clic sobre el título o una línea del encabezado en la vista previa para cambiar su texto, tamaño y color.</p>
    @endif
</div>
