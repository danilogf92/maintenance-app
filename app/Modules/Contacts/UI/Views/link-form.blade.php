<form wire:submit="saveLink" class="contact-links-form" aria-label="Configurar vínculo" x-data="{}" x-init="$nextTick(() => { $refs.heading.focus({preventScroll:true}); $el.scrollIntoView({block:'nearest'}); })" @keydown.escape.stop="$wire.cancelLink()">
 <header><div><span class="contact-link-kind">{{ $editLinkId ? 'Configuración' : 'Nuevo vínculo' }}</span><h4 tabindex="-1" x-ref="heading">{{ $editLinkId ? 'Editar vínculo' : ($contactId ? 'Elegir organización' : 'Elegir contacto') }}</h4></div><button type="button" class="contact-links-icon-button" aria-label="Cerrar configuración del vínculo" wire:click="cancelLink"><x-ui.icon name="x" /></button></header>
 <div class="contact-links-form-fields">
 @if($contactId)<label class="record-editor-field">Tipo de organización<select wire:model.live="selectionType" @disabled($editLinkId)>@foreach($parentTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>@endif
 @if(!$editLinkId)
 <details class="contact-links-advanced"><summary>Buscar por nombre</summary><label class="record-editor-field">{{ $contactId ? 'Nombre de la organización' : 'Nombre de la persona' }}<input type="search" wire:model.live.debounce.350ms="selectionSearch" placeholder="Escribe para filtrar las opciones"></label></details>
 @endif
 <label class="record-editor-field"><span>{{ $contactId ? 'Organización' : 'Persona' }} <span aria-hidden="true">*</span></span><select wire:model="selectionId" required @disabled($editLinkId)><option value="">Selecciona una opción…</option>@foreach($options as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
 <label class="record-editor-field">Función en esta organización<select wire:model="category">@foreach($categories as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
 <div class="contact-links-primary-option"><label class="contact-links-check"><input type="checkbox" wire:model="isPrimary"> Usar como contacto principal</label><p>Sustituirá al principal anterior de esta organización.</p></div>
 </div>
 <footer class="contact-links-form-actions"><button type="submit" class="contact-links-button" wire:loading.attr="disabled" wire:target="saveLink"><x-ui.icon name="check" /><span wire:loading.remove wire:target="saveLink">Guardar vínculo</span><span wire:loading wire:target="saveLink">Guardando…</span></button><button type="button" class="contact-links-secondary" wire:click="cancelLink">Cancelar</button></footer>
</form>
