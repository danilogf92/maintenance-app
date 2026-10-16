<x-filament-panels::page>
<link rel="stylesheet" href="{{ asset('css/report-template-editor.css') }}?v=11.38.7">
<div class="rt-editor" x-data="{ blocksOpen: true, propertiesOpen: true, previewOpen: true }"
    x-init="$store.sidebar.close()"
    :class="{ 'rt-no-blocks': !blocksOpen, 'rt-no-properties': !propertiesOpen, 'rt-no-preview': !previewOpen }"
    x-on:message.window="if ($refs.paper && $event.source === $refs.paper.contentWindow && $event.data?.type === 'report-template-select' && Number.isInteger($event.data.block) && ($event.data.column === null || ['left','center','right'].includes($event.data.column)) && ($event.data.line === null || Number.isInteger($event.data.line))) { propertiesOpen = true; $wire.selectPreviewElement($event.data.block, $event.data.column, $event.data.line); } else if ($refs.paper && $event.source === $refs.paper.contentWindow && $event.data?.type === 'report-data-select' && Number.isInteger($event.data.block) && Number.isInteger($event.data.column) && ($event.data.row === null || Number.isInteger($event.data.row))) { propertiesOpen = true; $wire.selectDataItem($event.data.block, $event.data.column, $event.data.row); }">
    @include('report-templates::Partials.toolbar')
    <div class="rt-panel-controls" aria-label="Paneles del editor">
        <button type="button" @click="blocksOpen = !blocksOpen" :aria-expanded="blocksOpen" x-text="blocksOpen ? 'Ocultar bloques' : 'Mostrar bloques'"></button>
        <button type="button" @click="propertiesOpen = !propertiesOpen" :aria-expanded="propertiesOpen" x-text="propertiesOpen ? 'Ocultar propiedades' : 'Mostrar propiedades'"></button>
        <button type="button" @click="previewOpen = !previewOpen" :aria-expanded="previewOpen" x-text="previewOpen ? 'Ocultar vista previa' : 'Mostrar vista previa'"></button>
    </div>
    @if($errors->any())
    <div class="rt-error" role="alert"><strong>Revisa la configuración:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="rt-workspace">
        @include('report-templates::Partials.block-list')
        <section class="rt-canvas" x-show="previewOpen" aria-label="Vista previa del informe">
            <div class="rt-preview-label"><strong>Vista rápida</strong><span>Haz clic en un bloque o texto para editarlo</span></div>
            <iframe x-ref="paper" class="rt-paper" title="Vista rápida del informe" sandbox="allow-scripts" srcdoc="{{ $previewHtml }}"></iframe>
            <p class="rt-help">El recuadro azul indica tu selección. No se imprime. Paginación exacta: «PDF de prueba».</p>
        </section>
        <aside class="rt-panel rt-properties" x-show="propertiesOpen">
            <h2>Propiedades del bloque</h2>
            @if(($blockData['type'] ?? '') === 'data_table')
                @include('report-templates::Partials.data-editor')
                <details class="rt-advanced"><summary>Configuración completa y opciones avanzadas</summary>{{ $this->form }}</details>
            @else
                @include('report-templates::Partials.selected-element')
                {{ $this->form }}
            @endif
            @if(in_array($blockData['type'] ?? '', ['activities', 'components']))
            <details class="rt-rule"><summary>Continuación automática</summary><p>Las tablas pasan a otra hoja. Cada foto se mantiene con su descripción.</p></details>
            @endif
        </aside>
    </div>
    @include('report-templates::Partials.pdf-status')
</div>
</x-filament-panels::page>
