<div class="rt-signature-settings">
    <label>Origen de la firma<select aria-label="Origen de la firma" wire:model.live="{{ $signaturePath }}.source">@foreach(\App\Modules\ReportTemplates\Configuration\Signature::sources() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
    <label>Texto bajo la firma<input maxlength="120" wire:model.live.debounce.600ms="{{ $signaturePath }}.caption"></label>
    <details class="rt-compact-settings"><summary>Tamaño, línea y nombre de firma</summary>
        <label>Ancho de firma (mm)<input type="number" min="15" max="160" wire:model.live.debounce.600ms="{{ $signaturePath }}.width"></label>
        <label>Alto de firma (mm)<input type="number" min="5" max="60" wire:model.live.debounce.600ms="{{ $signaturePath }}.height"></label>
        <label class="rt-check"><input type="checkbox" wire:model.live="{{ $signaturePath }}.show_line"> Mostrar línea</label>
        <label>Color de línea (HEX)<input maxlength="7" wire:model.live.debounce.600ms="{{ $signaturePath }}.line_color"></label>
        <label class="rt-check"><input type="checkbox" wire:model.live="{{ $signaturePath }}.show_name"> Mostrar nombre</label>
        <label>Nombre automático<select aria-label="Nombre automático" wire:model.live="{{ $signaturePath }}.name_variable"><option value="">Nombre manual</option>@foreach(\App\Modules\ReportTemplates\Configuration\Variables::options() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
        <label>Nombre manual<input maxlength="120" wire:model.live.debounce.600ms="{{ $signaturePath }}.name"></label>
    </details>
    <p class="rt-help">Sin firma digital de este informe se muestra el espacio para firmar. No se guarda una firma reutilizable en la plantilla.</p>
</div>
