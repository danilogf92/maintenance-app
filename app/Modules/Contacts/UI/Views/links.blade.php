<section class="contact-links" aria-label="Vínculos de contactos">
 <header class="contact-links-heading">
  <div><h3>{{ $contactId ? 'Organizaciones vinculadas' : 'Personas de contacto' }}</h3>
   <p>{{ $contactId ? 'Define la función de esta persona en cada organización.' : 'Consulta quién puede ayudarte y cómo comunicarte.' }}</p>
  </div>
  <span class="contact-links-count">{{ $items->total() }} {{ $items->total() === 1 ? 'vínculo' : 'vínculos' }}</span>
 </header>
 @if($canManage)
 <div class="contact-links-toolbar">
  <button type="button" class="contact-links-button" wire:click="beginLink" wire:loading.attr="disabled" wire:target="beginLink"><x-ui.icon name="plus" />{{ $contactId ? 'Vincular organización' : 'Vincular contacto' }}</button>
  @if(!$contactId)<a class="contact-links-secondary" href="{{ route('operations.contacts', ['new_for_'.$parentType => $parentId]) }}"><x-ui.icon name="users" />Nuevo contacto</a>@endif
 </div>
 @endif
 <div class="contact-links-filters">
  <label class="contact-links-search"><x-ui.icon name="search" /><span class="sr-only">Buscar vínculos</span><input type="search" aria-label="Buscar vínculos" placeholder="{{ $contactId ? 'Buscar organización…' : 'Buscar nombre o correo…' }}" wire:model.live.debounce.350ms="search"></label>
  <label class="contact-links-check"><input type="checkbox" wire:model.live="includeInactive"> Mostrar desactivados</label>
 </div>
 @if($notice)<p role="status" class="contact-links-notice"><x-ui.icon name="check" />{{ $notice }}</p>@endif
 <x-record-editor.validation-summary />
 @if($linkFormOpen && $canManage) @include('contacts::link-form') @endif
 @if($pendingDeactivate) @include('contacts::link-confirmation') @endif
 <div class="contact-links-list" aria-label="Lista de vínculos">
 @forelse($items as $link)
  @include('contacts::link-card')
 @empty
  <div class="contact-links-empty"><x-ui.icon name="users" /><h4>{{ $search !== '' ? 'Sin coincidencias' : 'Sin vínculos para mostrar' }}</h4><p>{{ $search !== '' ? 'Prueba con otro nombre o correo.' : 'Vincula un registro existente o crea un nuevo contacto desde su organización.' }}</p></div>
 @endforelse
 </div>
 @if($items->hasPages())<div class="contact-links-pagination">{{ $items->links() }}</div>@endif
 <p class="contact-links-loading" wire:loading.delay role="status">Actualizando vínculos…</p>
</section>
