<form wire:submit="saveVisit" class="planning-edit" aria-label="Editar visita programada" x-data="{}" x-init="$nextTick(() => $el.scrollIntoView({block:'nearest'}))" @keydown.escape.stop="$wire.cancelEdit()">
 <h4>Editar visita</h4>
 <label>{{ $dailyVisit ? 'Fecha prevista' : 'Mes previsto' }}@if($dailyVisit)<input type="date" wire:model="scheduled_for" required>@else<input type="month" wire:model="scheduled_month" required>@endif</label>
 <label>Técnico<select wire:model="visitTechnician"><option value="">Sin asignar todavía</option>@foreach($technicians as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label>
 <label>Estado<select wire:model="visitStatus"><option value="planned">Programada</option><option value="cancelled">Cancelada</option></select></label>
 <label>Indicaciones<textarea wire:model="visitNotes" maxlength="5000" rows="3"></textarea></label>
 <footer><button type="submit" class="planning-button" wire:loading.attr="disabled">Guardar visita</button><button type="button" class="planning-secondary" wire:click="cancelEdit">Cancelar</button></footer>
</form>
