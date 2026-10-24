<div class="min-h-0 flex-1 overflow-auto p-5">
 <h2 class="text-lg font-semibold">Pasos pendientes de relación</h2>
 <p class="record-editor-hint">Se conservan íntegros. Para vincularlos faltan sus protocolos padre o confirmar su pertenencia.</p>
 @forelse($pendingSteps as $step)
  @include('maintenance-protocols::step')
 @empty<p>No hay pasos pendientes.</p>@endforelse
</div>
