<div>
 <h3>{{ $definition['label'] }}</h3>
 <input class="institution-related-search" wire:model.live.debounce.400ms="search" placeholder="Buscar usuarios relacionados…" aria-label="Buscar usuarios de esta institución">
 <div style="overflow-x:auto">
 <table class="institution-related-table">
 <thead><tr>@foreach($definition['columns'] as $column)<th>{{ \App\Modules\User\UserDefinition::columns()[$column] ?? $column }}</th>@endforeach</tr></thead>
 <tbody>
 @forelse($items as $item)
 <tr wire:key="related-user-{{ $item->id }}">
 @foreach($definition['columns'] as $column)
 <td>@if($column === 'name' && in_array('open', $definition['actions'], true))
  <button type="button" wire:click="openUser({{ $item->id }})">{{ $item->name }}</button>
 @else{{ $item->$column ?: '—' }}@endif</td>
 @endforeach
 </tr>
 @empty<tr><td colspan="{{ count($definition['columns']) }}">No hay usuarios asociados.</td></tr>@endforelse
 </tbody></table>
 </div>
 {{ $items->links() }}
 @if($relatedUserId)
  @livewire('institution-user-editor', ['institutionId' => $institutionId, 'recordId' => $relatedUserId], key('institution-child-'.$childSession))
 @endif
</div>
