@php
 $parent = $link->client_id ? $link->client : $link->institution;
 $displayName = $contactId ? $parent?->name : $link->contact->name;
 $destination = $contactId ? route('operations.'.($link->client_id ? 'clients' : 'institutions'), ['record' => $link->parentId()]) : route('operations.contacts', ['record' => $link->contact_id]);
 $inactive = !$link->active || !$link->contact->active;
@endphp
<article wire:key="contact-link-{{ $link->id }}" class="contact-link-card {{ $link->is_primary ? 'is-primary' : '' }} {{ $editLinkId === $link->id ? 'is-editing' : '' }} {{ $inactive ? 'is-inactive' : '' }}" aria-label="{{ $displayName }}">
 <div class="contact-link-card-top">
  <span class="contact-link-avatar" aria-hidden="true"><x-ui.icon :name="$contactId ? 'building' : 'users'" /></span>
  <div class="contact-link-identity"><span class="contact-link-kind">{{ $contactId ? ($link->client_id ? 'Cliente' : 'Institución') : ($link->contact->position ?: 'Contacto') }}</span>
   <a class="contact-link-name" href="{{ $destination }}">{{ $displayName }}</a>
   @if(!$contactId && $link->contact->department)<p>{{ $link->contact->department }}</p>@endif
  </div>
 </div>
 <div class="contact-link-badges">
  <span class="contact-link-badge">{{ $categories[$link->category] ?? $link->category }}</span>
  @if($link->is_primary)<span class="contact-link-badge primary"><x-ui.icon name="star" />Principal</span>@endif
  @if($inactive)<span class="contact-link-badge inactive">{{ !$link->active ? 'Vínculo desactivado' : 'Contacto inactivo' }}</span>@endif
  @if($editLinkId === $link->id)<span class="contact-link-badge editing">Editando</span>@endif
 </div>
 @if(!$contactId && ($link->contact->email || $link->contact->phone))
 <div class="contact-link-channels">
  @if($link->contact->email)<div><x-ui.icon name="mail" /><span>{{ $link->contact->email }}</span></div>@endif
  @if($link->contact->phone)<div><x-ui.icon name="phone" /><span>{{ $link->contact->phone }}{{ $link->contact->extension ? ' · Ext. '.$link->contact->extension : '' }}</span></div>@endif
 </div>
 @endif
 @if($canManage)
 <footer class="contact-link-card-actions">
  <button type="button" wire:click="editLink({{ $link->id }})" wire:loading.attr="disabled" wire:target="editLink"><x-ui.icon name="pencil" />{{ $link->active ? 'Editar' : 'Reactivar' }}</button>
  @if($link->active)<button type="button" class="contact-link-unlink" wire:click="requestDeactivate({{ $link->id }})" wire:loading.attr="disabled" wire:target="requestDeactivate"><x-ui.icon name="unlink" />Desvincular</button>@endif
 </footer>
 @endif
</article>
