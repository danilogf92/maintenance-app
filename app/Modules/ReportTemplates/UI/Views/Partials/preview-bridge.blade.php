<style>
[data-preview-block], [data-preview-column], [data-preview-line] { cursor: pointer; }
[data-preview-block]:hover { outline: 1px dashed #94a3b8; outline-offset: 2px; }
[data-preview-line]:hover, [data-preview-column]:hover { outline: 1px dashed #2563eb; }
@if($column === null)
[data-preview-block="{{ $selected }}"] { outline: 2px solid #2563eb; outline-offset: 3px; }
@elseif($line === null)
[data-preview-block="{{ $selected }}"] [data-preview-column="{{ $column }}"] { outline: 2px solid #2563eb; background: #eff6ff; }
@else
[data-preview-block="{{ $selected }}"] [data-preview-column="{{ $column }}"] [data-preview-line="{{ $line }}"] { outline: 2px solid #2563eb; outline-offset: 2px; background: #eff6ff; }
@endif
</style>
@php
    $selectionSelector = '[data-preview-block="'.$selected.'"]';
    if ($column !== null) { $selectionSelector .= ' [data-preview-column="'.$column.'"]'; }
    if ($line !== null) { $selectionSelector .= ' [data-preview-line="'.$line.'"]'; }
    if ($dataRow !== null) { $selectionSelector .= ' [data-preview-data-column="'.$dataColumn.'"][data-preview-data-row="'.$dataRow.'"]'; }
@endphp
<style>
@if($dataRow !== null)
[data-preview-block="{{ $selected }}"] [data-preview-data-column="{{ $dataColumn }}"][data-preview-data-row="{{ $dataRow }}"] { outline: 2px solid #2563eb; outline-offset: -1px; background: #eff6ff; }
@endif
[data-preview-data-column] { cursor: pointer; }
[data-preview-data-column]:hover { outline: 1px dashed #2563eb; }
</style>
<script>
(() => {
    const selection = document.querySelector(@js($selectionSelector));
    selection?.scrollIntoView({block: 'nearest'});
    const choose = (target) => {
        const block = target.closest('[data-preview-block]');
        if (!block) return;
        const dataCell = target.closest('[data-preview-data-column]');
        if (dataCell) {
            parent.postMessage({type: 'report-data-select', block: Number(block.dataset.previewBlock),
                column: Number(dataCell.dataset.previewDataColumn), row: dataCell.hasAttribute('data-preview-data-row') ? Number(dataCell.dataset.previewDataRow) : null}, '*');
            return;
        }
        const column = target.closest('[data-preview-column]');
        const line = target.closest('[data-preview-line]');
        parent.postMessage({type: 'report-template-select', block: Number(block.dataset.previewBlock),
            column: column?.dataset.previewColumn ?? null, line: line ? Number(line.dataset.previewLine) : null}, '*');
    };
    document.querySelectorAll('[data-preview-block], [data-preview-column], [data-preview-line], [data-preview-data-column]').forEach(el => {
        el.tabIndex = 0;
        el.setAttribute('role', 'button');
        el.setAttribute('aria-label', el.hasAttribute('data-preview-line') ? 'Editar esta línea' : 'Editar este elemento');
    });
    document.addEventListener('click', event => choose(event.target));
    document.addEventListener('keydown', event => {
        if (['Enter', ' '].includes(event.key) && event.target.matches('[data-preview-block], [data-preview-column], [data-preview-line], [data-preview-data-column]')) {
            event.preventDefault(); choose(event.target);
        }
    });
})();
</script>
