<div role="alertdialog" aria-label="Desactivar vínculo" class="contact-links-confirm" x-data="{}" x-init="$nextTick(() => { $refs.heading.focus({preventScroll:true}); $el.scrollIntoView({block:'nearest'}); })" @keydown.escape.stop="$wire.cancelDeactivate()">
 <h4 tabindex="-1" x-ref="heading">¿Desvincular este contacto?</h4><p>La persona y su historial se conservarán. Puedes reactivar el vínculo después.</p>
 <div class="contact-links-toolbar"><button type="button" class="contact-links-danger" wire:click="deactivate" wire:loading.attr="disabled" wire:target="deactivate">Desactivar vínculo</button><button type="button" class="contact-links-secondary" wire:click="cancelDeactivate">Cancelar</button></div>
</div>
