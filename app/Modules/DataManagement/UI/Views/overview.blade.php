@php($sourceTables = collect($inventory['tables'] ?? []))
<div class="dm-metrics">
 @foreach(['Tablas detectadas'=>$sourceTables->count(),'Registros detectados'=>$sourceTables->sum('records_valid'),'Adjuntos detectados'=>$sourceTables->sum('attachments'),'Sin correspondencia'=>collect($rows)->whereNull('target')->count()] as $label=>$value)
 <div class="dm-card"><span>{{ $label }}</span><strong>{{ number_format($value,0,',','.') }}</strong></div>
 @endforeach
</div>
<div class="dm-grid">
 <section class="dm-panel"><h3>Diagnóstico de origen</h3>
  @if($inventory)<p><strong>Ejecución:</strong> {{ $inventory['run_id'] }}</p><p class="dm-path">{{ $inventory['source'] }}</p><p>Revisado: {{ $inventory['loaded_at'] }}</p>
  @else<p>Todavía no se ha cargado un inventario. Usa LeerDatos.cmd y después lee el último resultado o sube su ZIP.</p>@endif
  @if($canManage)
  <x-filament::button wire:click="loadLatest" wire:loading.attr="disabled" icon="heroicon-o-arrow-path">Leer último diagnóstico</x-filament::button>
  <p class="dm-help">Lee el resultado más reciente de data/resultado dentro del proyecto.</p>
  <label class="dm-label">Subir diagnóstico existente<input type="file" wire:model="inventoryUpload" accept=".zip,.json"></label>
  <p class="dm-help">ZIP de diagnóstico o carpetas.json, hasta 10 MiB.</p>
  <span wire:loading wire:target="inventoryUpload" role="status">Preparando archivo…</span>
  <x-filament::button color="gray" wire:click="loadUpload" wire:loading.attr="disabled" wire:target="loadUpload,inventoryUpload">Revisar archivo seleccionado</x-filament::button>
  @endif
 </section>
 <section class="dm-panel"><h3>Orden propuesto para cuadrar los datos</h3>
  <ol class="dm-steps"><li>Clientes e instituciones: identificar nombres, RUC y referencias.</li><li>Equipos: resolver cliente, institución, marca y modelo sin confundir IDs de origen y locales.</li><li>Contratos: revisar cliente y equipos asociados; completar sus períodos de cobertura.</li><li>Tickets y servicios: mantener vínculos y técnicos históricos.</li><li>Actividades, protocolos y archivos: revisar pertenencia y datos pendientes.</li></ol>
  <button type="button" class="dm-link" wire:click="selectTab('mappings')">Revisar correspondencias →</button>
 </section>
</div>
<section class="dm-panel"><h3>Estado de las acciones de carga</h3><p>Disponible: clientes, centros e historiales QC/RC en cola, con control de duplicados, asignaciones verificadas y resultado descargable.</p>
 <button type="button" class="dm-link" wire:click="selectTab('imports')">Preparar y cargar clientes y centros →</button>
 <div class="dm-actions-status"><span><strong>Update</strong> · Pendiente de adaptar y validar la carga.</span><span><strong>Erase</strong> · Pendiente de definir el borrado según dependencias.</span><span><strong>Seed</strong> · Pendiente de validar la reconstrucción.</span></div>
</section>
