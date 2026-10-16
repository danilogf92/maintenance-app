@if($generation)
<section class="rt-pdf-status" @if(in_array($generation->status, ['queued','processing']) && !$generation->expires_at->isPast()) wire:poll.3s @endif aria-live="polite">
    <div>
    @if($generation->expires_at->isPast())
        <strong>Prueba expirada</strong><p>Pulsa «PDF de prueba» para generar otra.</p>
    @elseif($generation->status === 'ready')
        <strong>PDF listo</strong><p>Abre el documento para evaluar las hojas, imprimirlo o descargarlo. Disponible hasta {{ $generation->expires_at->format('H:i') }} UTC.</p>
        <x-filament::button tag="a" href="{{ route('reports.preview', $generation->id) }}" target="_blank" rel="noopener" icon="heroicon-o-arrow-top-right-on-square">Abrir PDF en otra pestaña</x-filament::button>
    @elseif($generation->status === 'failed')
        <strong>No se pudo generar el PDF</strong><p>{{ $generation->error }}</p>
        <x-filament::button wire:click="testPdf">Reintentar con la configuración actual</x-filament::button>
    @else
        <strong>{{ $generation->status === 'queued' ? 'En cola' : 'Generando PDF' }}</strong>
        <p>La edición puede continuar. Esta prueba usa los datos y el diseño capturados al solicitarla.</p>
    @endif
    </div>
</section>
@endif
