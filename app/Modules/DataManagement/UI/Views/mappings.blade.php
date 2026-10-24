<div class="dm-toolbar"><label class="dm-label">Buscar tabla<input type="search" wire:model.live.debounce.350ms="search" placeholder="Nombre, carpeta o entidad…"></label>
 @if($canManage && $inventory)<div class="dm-toolbar-actions"><x-filament::button color="gray" wire:click="reloadMappings">Descartar cambios</x-filament::button><x-filament::button wire:click="saveMappings" wire:loading.attr="disabled">Guardar correspondencias</x-filament::button></div>@endif
</div>
<p class="dm-help">El destino se guarda en el único archivo data/correspondencias.json. Los conteos en BD son actuales; coincidir en cantidad no confirma que los registros sean los mismos.</p>
<div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Tabla de origen</th><th>Registros detectados</th><th>Entidad del desarrollo</th><th>Registros en BD</th><th>Estado</th><th></th></tr></thead><tbody>
 @forelse($visibleRows as $row)
 <tr wire:key="mapping-{{ $row['key'] }}" class="{{ $selectedRow && $selectedRow['key']===$row['key'] ? 'is-selected' : '' }}">
  <td><strong>{{ $row['source']['name'] }}</strong><small class="dm-path">{{ $row['source']['folder'] }}</small></td><td>{{ $row['source']['records_valid'] }}</td>
  <td><select wire:model.live="mappings.{{ $row['key'] }}" aria-label="Destino de {{ $row['source']['name'] }}" @disabled(!$canManage)>
   <option value="">Pendiente / sin correspondencia</option>
   @if($row['target'] && !isset($targets[$row['target']]))<option value="{{ $row['target'] }}" disabled>Destino no disponible: {{ $row['target'] }}</option>@endif
   @foreach($targets as $table=>$definition)<option value="{{ $table }}">{{ $definition['label'] }} · {{ $table }}</option>@endforeach
  </select></td><td>{{ $row['database_count'] ?? '—' }}</td><td><span class="dm-state">{{ $row['state'] }}</span></td>
  <td><button type="button" class="dm-link" wire:click="selectSource('{{ $row['key'] }}')">Revisar</button></td>
 </tr>
 @empty<tr><td colspan="6">No hay tablas que mostrar.</td></tr>@endforelse
</tbody></table></div>
<p class="dm-help">{{ $visibleRows->count() }} tablas en esta vista. Todas se muestran sin paginación.</p>
@if($selectedRow)
 <section class="dm-panel dm-detail" x-data="{}" @data-source-selected.window="$nextTick(() => $el.scrollIntoView({block: 'start', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'}))" wire:key="source-detail-{{ $selectedRow['key'] }}">
  <div class="dm-intro"><h3>{{ $selectedRow['source']['name'] }}</h3><span class="dm-state">{{ $selectedRow['target_label'] }}</span></div>
  <p class="dm-path">{{ $selectedRow['source']['folder'] }}</p>
  <div class="dm-note"><strong>Qué revisar</strong><p>{{ $selectedRow['hint']['note'] }}</p>
   @if($selectedRow['hint']['target'])<p>Relación posible para estudiar: <code>{{ $selectedRow['hint']['target'] }}</code>. Esta propuesta no se guarda automáticamente.</p>@endif
  </div>
  <div class="dm-grid">
   <details class="dm-panel" open><summary>Campos recibidos · {{ count($selectedRow['source']['fields']) }}</summary><div class="dm-tags">@foreach($selectedRow['source']['fields'] as $field)<code>{{ $field }}</code>@endforeach</div></details>
   <details class="dm-panel" open><summary>Campos actuales del destino</summary>
    @if($selectedRow['implemented'])<table class="dm-table"><thead><tr><th>Campo</th><th>Tipo</th><th>Obligatorio en BD</th></tr></thead><tbody>
     @foreach($selectedRow['columns'] as $column)<tr><td><code>{{ $column['name'] }}</code></td><td>{{ $column['type_name'] }}</td><td>{{ !$column['nullable'] && $column['default']===null && !$column['auto_increment'] ? 'Sí' : 'No / valor automático' }}</td></tr>@endforeach
    </tbody></table>@else<p>Selecciona una entidad existente para comparar sus campos.</p>@endif
   </details>
  </div>
  <p class="dm-help">Esta comparación muestra nombres y estructura; no traduce campos ni verifica los valores originales.</p>
 </section>
@endif
