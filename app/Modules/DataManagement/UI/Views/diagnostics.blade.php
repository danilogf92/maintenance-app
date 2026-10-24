<section class="dm-panel"><h3>Diagnóstico de la revisión</h3><p>Descargar diagnóstico conserva el inventario revisado, las correspondencias guardadas, las relaciones actuales, errors.json, status.json y ack.json. No incluye los registros originales ni sus fotografías y PDF.</p>
 @if($inventory)<x-filament::button wire:click="downloadReview" wire:loading.attr="disabled">Descargar diagnóstico</x-filament::button>@endif
 <p class="dm-help">ACK indica que esta operación es una revisión y que no se ha importado ningún registro.</p>
</section>
@if($inventory)
<section class="dm-panel"><h3>Observaciones del lector</h3>
 <div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Tipo</th><th>Código</th><th>Carpeta / archivo</th><th>Detalle</th></tr></thead><tbody>
 @forelse($inventory['issues'] ?? [] as $issue)<tr><td>{{ $issue['severity']==='error' ? 'Error' : 'Observación' }}</td><td><code>{{ $issue['code'] }}</code></td><td class="dm-path">{{ $issue['path'] }}</td><td>{{ $issue['message'] }}</td></tr>
 @empty<tr><td colspan="4">No hay observaciones del lector cargadas.</td></tr>@endforelse
 </tbody></table></div>
</section>
@endif
