<div wire:poll.5s>
 @include('data-management::history-import')
 <section class="dm-panel"><h3>Clientes y centros médicos</h3>
  <p>Lee los datos originales de DataSanitized y conserva sus identificadores. Los registros nuevos aparecen en Clientes e Instituciones.</p>
  <p class="dm-help">Se cargan los campos base: nombre, RUC, correo, teléfono y dirección según la entidad. Los demás datos originales se conservan para completar su correspondencia después.</p>
  <ol class="dm-steps"><li>La actualización intenta preparar los datos automáticamente. Para repetir la lectura, ejecuta <code>data\PrepararClientes.cmd</code>. Usa la ruta de <code>data/configuracion.ini</code>.</li>
   <li>Comprueba las cantidades preparadas y pulsa <strong>Cargar clientes y centros</strong>.</li>
   <li>Descarga el resultado para revisar los campos y relaciones pendientes.</li></ol>
  @if($preparedError)<p role="alert" class="dm-errors">{{ $preparedError }}</p>@endif
  @if($prepared)
   <div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Origen</th><th>Destino</th><th>Preparados</th></tr></thead><tbody>
    @foreach($prepared['tables'] as $entity=>$table)<tr><td>{{ $table['source_name'] }}</td><td>{{ $entity }}</td><td>{{ number_format($table['records'],0,',','.') }}</td></tr>@endforeach
   </tbody></table></div>
   @if($canManage)<x-filament::button wire:click="startCatalogImport" wire:loading.attr="disabled" icon="heroicon-o-arrow-up-tray"
    :disabled="$importRuns->contains(fn ($run) => in_array($run->status, ['queued','processing'], true))">Cargar clientes y centros</x-filament::button>@endif
  @else<p class="dm-help">Todavía no hay un paquete preparado. El diagnóstico de carpetas contiene cantidades y nombres de campos; para importar se necesitan los registros originales.</p>@endif
  <p class="dm-help">Esta carga inicial agrega registros. Repetir el mismo paquete no duplica datos. Los cambios de origen y los códigos ocupados se señalan como conflictos para revisión.</p>
 </section>
 <section class="dm-panel"><h3>Resultado de las cargas</h3>
  @forelse($importRuns as $run)
   <details class="dm-panel" @if($loop->first) open @endif wire:key="import-{{ $run->id }}"><summary>
    {{ $run->created_at->format('d/m/Y H:i') }} · {{ $run->operation==='histories' ? 'Historiales y equipos' : 'Clientes y centros' }} · {{ ['queued'=>'En cola','processing'=>'Procesando','completed'=>'Completado','completed_with_observations'=>'Completado con observaciones','failed'=>'Fallido'][$run->status] ?? $run->status }}
   </summary>
   <p class="dm-help">Referencia: {{ $run->id }}</p>
   @if(in_array($run->status,['queued','processing'],true))<p role="status">El proceso se ejecuta en segundo plano. El resultado se actualiza automáticamente.</p>@endif
   @if($run->summary)
    <div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Entidad</th><th>Leídos</th><th>Creados</th><th>Actualizados</th><th>Ya existentes</th><th>Conflictos</th><th>Rechazados</th></tr></thead><tbody>
     @foreach($run->summary as $entity=>$count)<tr><td>{{ \App\Modules\DataManagement\Configuration\Entities::label($entity) }}</td>@foreach(['read','created','updated','existing','conflict','rejected'] as $key)<td>{{ $count[$key] ?? 0 }}</td>@endforeach</tr>@endforeach
    </tbody></table></div>
   @endif
   @include('data-management::assignment-results')
   @if($run->issues)
    <details><summary>Observaciones: {{ count($run->issues) }}</summary><div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Tipo</th><th>Entidad</th><th>ID de origen</th><th>Campo / detalle</th></tr></thead><tbody>
     @foreach($run->issues as $issue)<tr><td>{{ $issue['severity']==='error' ? 'Error' : 'Observación' }}</td><td>{{ $issue['entity'] ?? 'Carga' }}</td><td>{{ $issue['source_id'] ?? '—' }}</td>
      <td>@if(isset($issue['field']))<strong>{{ $issue['field'] }}</strong> · @endif{{ $issue['message'] ?? '' }} <code>{{ $issue['code'] }}</code></td></tr>@endforeach
    </tbody></table></div></details>
   @endif
   @if($run->finished_at)<x-filament::button color="gray" wire:click="downloadImport('{{ $run->id }}')" wire:loading.attr="disabled" icon="heroicon-o-arrow-down-tray">Descargar resultado y errores</x-filament::button>@endif
   </details>
  @empty<p>No se ha iniciado ninguna carga desde este apartado.</p>@endforelse
 </section>
 <section class="dm-panel"><h3>Relaciones que siguen pendientes</h3>
  <p>Los equipos apuntan a <code>customer_history</code> y <code>equipment_medical_centers_history</code>. Esas referencias identifican historiales, no clientes ni centros directamente.</p>
  <p>La carga QC/RC resuelve las asignaciones verificables. El resultado descargable incluye <code>assignment-results.json</code> con cada vínculo resuelto o pendiente. Ciudad y contactos se revisan por separado.</p>
  <p><a href="{{ url('/operations/clients') }}">Ver clientes</a> · <a href="{{ url('/operations/institutions') }}">Ver instituciones</a></p>
 </section>
</div>
