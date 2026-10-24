<div class="rt-toolbar">
    <details class="rt-template-fold"><summary>Plantilla: {{ $metadata['name'] ?? 'Sin nombre' }}</summary><div class="rt-template-info">
        <label for="rt-name">Nombre de la plantilla</label>
        <input id="rt-name" wire:model.live.debounce.600ms="metadata.name" maxlength="120">
        <label for="rt-template-switch">Cambiar plantilla</label>
        <select id="rt-template-switch" onchange="if(this.value)window.location.href=this.value">
            @foreach($templates as $template)<option value="{{ \App\Modules\ReportTemplates\UI\ReportTemplateResource::getUrl('edit', ['record' => $template->id]) }}" @selected($template->id === $templateId)>{{ $template->name }}</option>@endforeach
        </select>
    </div>
    </details>
    <div class="rt-actions">
        <x-filament::button color="gray" wire:click="testPdf" wire:loading.attr="disabled" icon="heroicon-o-document-magnifying-glass">PDF de prueba</x-filament::button>
        <x-filament::button wire:click="save" wire:loading.attr="disabled" icon="heroicon-o-check">Guardar borrador</x-filament::button>
        <x-filament::button color="gray" wire:click="publish" wire:confirm="¿Guardar y publicar una nueva versión de esta plantilla?" wire:loading.attr="disabled">Publicar versión</x-filament::button>
    </div>
</div>
<details class="rt-settings">
    <summary>Papel, diseño y datos de prueba</summary>
    <div class="rt-settings-grid">
        <label>Papel<select wire:model.live="definition.page.paper"><option>A4</option><option>Letter</option></select></label>
        <label>Orientación<select wire:model.live="definition.page.orientation"><option value="portrait">Vertical</option><option value="landscape">Horizontal</option></select></label>
        <label>Márgenes (mm)<input type="number" min="10" max="30" wire:model.live.debounce.600ms="definition.page.margin"></label>
        <label>Tipo de letra general<select wire:model.live="definition.page.font_family">@foreach(\App\Modules\ReportTemplates\Configuration\TextStyle::fonts() as $key => $name)<option value="{{ $key }}">{{ $name }}</option>@endforeach</select></label>
        <label>Tamaño general (pt, como Word)<input type="number" min="6" max="72" step="0.5" list="rt-word-sizes" wire:model.live.debounce.600ms="definition.page.font_size"></label>
        <label>Color de títulos (HEX)<input type="text" maxlength="7" pattern="#[0-9A-Fa-f]{6}" placeholder="#00858B" wire:model.live.debounce.600ms="definition.page.color"></label>
        <label>Pie de página<input maxlength="120" wire:model.live.debounce.600ms="definition.page.footer"></label>
        <p>La vista previa usa una bitácora recibida. Las tablas pendientes permanecen vacías.</p>
        <label class="rt-check"><input type="checkbox" wire:model.live="definition.page.repeat_brand"> Repetir marca en cada hoja</label>
        <label>Descripción<textarea maxlength="1000" wire:model="metadata.description"></textarea></label>
    </div>
</details>

<datalist id="rt-word-sizes">@foreach(\App\Modules\ReportTemplates\Configuration\TextStyle::sizes() as $size)<option value="{{ $size }}">@endforeach</datalist>
