<div class="rt-single-item" wire:key="item-{{ $selected }}-{{ $dataColumnIndex }}-{{ $dataRowIndex }}">
    <strong>Editando: {{ ($dataField['label'] ?? '') ?: 'Ítem '.($dataRowIndex + 1) }}</strong>
    <label>Etiqueta<input maxlength="80" wire:model.live.debounce.600ms="{{ $itemPath }}.label"></label>
    <label>Contenido<select aria-label="Contenido" wire:model.live="{{ $itemPath }}.mode"><option value="variable">Variable del sistema</option><option value="manual">Texto manual</option><option value="signature">Firma individual</option></select></label>
    @if($dataField['mode'] === 'variable')
        <label>Variable<select aria-label="Variable" wire:model.live="{{ $itemPath }}.variable">@foreach(\App\Modules\ReportTemplates\Configuration\Variables::options() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
    @elseif($dataField['mode'] === 'manual')
        <label>Valor manual<textarea maxlength="500" wire:model.live.debounce.600ms="{{ $itemPath }}.text"></textarea></label>
    @else
        @include('report-templates::Partials.DataEditor.signature', ['signaturePath' => $itemPath.'.signature'])
    @endif
    <label>Alineación del ítem (etiqueta + valor)<select aria-label="Alineación del ítem (etiqueta + valor)" wire:model.live="{{ $itemPath }}.item_alignment">
        <option value="inherit">Heredar de la columna</option><option value="left">Izquierda</option><option value="center">Centro</option><option value="right">Derecha</option><option value="legacy">Diseño anterior en celdas</option>
    </select></label>
    <details class="rt-compact-settings"><summary>Posición y orden</summary>
        <label>Sangría del ítem (mm)<input type="number" min="0" max="30" step="0.5" wire:model.live.debounce.600ms="{{ $itemPath }}.item_indent"></label>
        <div class="rt-item-actions">
            <button type="button" wire:click="moveDataItem(-1)" @disabled($dataRowIndex === 0)>Subir</button>
            <button type="button" wire:click="moveDataItem(1)" @disabled($dataRowIndex === count($dataFields) - 1)>Bajar</button>
            <button type="button" wire:click="removeDataItem" wire:confirm="¿Quitar este ítem?" @disabled(count($dataFields) <= 1)>Quitar ítem</button>
        </div>
    </details>
</div>
