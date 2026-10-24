@if(!$record)<p class="record-editor-hint">Guarda el registro para consultar sus relaciones.</p>
@elseif(!$related)<p class="record-editor-hint">No tienes permiso para consultar los registros relacionados.</p>
@else
 <section class="record-editor-section">
  <h3>{{ $related['label'] }} relacionados</h3>
  @if($related['module'] !== 'equipment' && \App\Modules\Workflow\Configuration\Permissions::allows($related['module'], 'manage'))
   <p><a href="{{ route('operations.'.$related['module'], ['new_for' => $record->id]) }}"><x-ui.icon name="plus" />Añadir {{ $related['module'] === 'brands' ? 'marca' : 'modelo' }}</a></p>
  @endif
  <div style="overflow:auto"><table class="record-related-table"><thead><tr><th>Código</th><th>Nombre</th></tr></thead><tbody>
  @forelse($related['items']->take(50) as $child)
   <tr wire:key="catalog-child-{{ $child->id }}"><td><a href="{{ route('operations.'.$related['module'], ['record' => $child->id]) }}"><x-ui.icon name="external-link" />{{ $child->code }}</a></td><td>{{ $child->name }}</td></tr>
  @empty<tr><td colspan="2">No hay registros relacionados.</td></tr>@endforelse
  </tbody></table></div>
  @if($related['items']->count() > 50)<p class="record-editor-hint">Se muestran los primeros 50. Abre el catálogo para buscar más registros.</p>@endif
 </section>
@endif
