<section class="dm-panel"><h3>Historiales y asignación actual de equipos</h3>
 <p>Conserva <strong>QC · Centro Asociado</strong> y <strong>RC · Clientes Asociados</strong>. Valida sus identificadores originales contra los clientes, centros y equipos ya cargados.</p>
 <p>La actualización prepara estos archivos desde tu carpeta configurada. Para repetir la lectura, ejecuta <code>data\PrepararHistoriales.cmd</code>.</p>
 @if($historyError)<p role="alert" class="dm-errors">{{ $historyError }}</p>@endif
 @if($historyPrepared)
  <div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Tabla</th><th>Origen</th><th>Registros preparados</th></tr></thead><tbody>
   @foreach($historyPrepared['tables'] as $table)<tr><td>{{ $table['source_table'] }}</td><td>{{ $table['source_name'] }}</td><td>{{ number_format($table['records'],0,',','.') }}</td></tr>@endforeach
  </tbody></table></div>
  @if($canManage)<x-filament::button wire:click="startHistoryImport" wire:loading.attr="disabled" icon="heroicon-o-link"
   :disabled="$importRuns->contains(fn ($run) => in_array($run->status,['queued','processing'],true))">Cargar historiales y vincular equipos</x-filament::button>@endif
 @else<p class="dm-help">No hay un paquete de historiales preparado. Comprueba que Centro_Asociado y Clientes_Asociados están en DataSanitized dentro del origen configurado.</p>@endif
 <p class="dm-help">Revisa primero la carga de clientes y centros. Se validan ambas direcciones de cada relación. Los empates, datos faltantes y destinos actuales diferentes quedan pendientes. Las asignaciones futuras se conservan sin adelantarlas.</p>
 <p class="dm-help">Si falta la correspondencia de QC o RC, se añade al único data/correspondencias.json al iniciar la carga; se respetan las correspondencias que ya guardaste.</p>
</section>
