<section class="planning-panel contract-coverages">
 <header><h3>Equipos y cumplimiento</h3><p>Cada equipo tiene su propia cobertura. La frecuencia y las visitas se definen en sus planes.</p></header>
 <p class="record-editor-hint">Una visita cuenta una vez cuando su ticket tiene un servicio completado. Se muestran los planes que puedes consultar.</p>
 @if($notice)<p class="planning-notice" role="status">{{ $notice }}</p>@endif
 <x-record-editor.validation-summary />
 <div class="contract-summary"><span><strong>{{ $coverages->count() }}</strong> equipos</span><span><strong>{{ $coverages->sum('committed') }}</strong> comprometidas</span><span><strong>{{ $coverages->sum('completed') }}</strong> cumplidas</span><span><strong>{{ $coverages->sum('overdue') }}</strong> vencidas programadas</span></div>
 @if($canManage && $contract->active)<button class="planning-button" type="button" wire:click="create"><x-ui.icon name="plus" />Añadir equipo</button>@endif
 @if($showForm)
 <form wire:submit="save" class="planning-edit">
  <h4>{{ $coverageId ? 'Editar cobertura' : 'Cobertura de equipo' }}</h4>
  <label>Buscar equipo<input type="search" wire:model.live.debounce.350ms="equipmentSearch" placeholder="Nombre, código o serie"></label>
  <label>Equipo<select wire:model="equipment_id" required><option value="">Selecciona un equipo</option>@foreach($equipmentOptions as $id=>$label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></label>
  <label>Inicio de cobertura<input type="month" wire:model="first_month" required></label>
  <label>Fin de cobertura<input type="month" wire:model="end_month" required></label>
  <label>Requerimientos particulares<textarea wire:model="requirements" rows="3" maxlength="5000"></textarea></label>
  <p>Las fechas pueden ser distintas para cada equipo. Si ya existen planes, se conservan equipo y vigencia.</p>
  <footer><button class="planning-button" type="submit" wire:loading.attr="disabled">Guardar cobertura</button><button class="planning-secondary" type="button" wire:click="cancel">Cancelar</button></footer>
 </form>
 @endif
 <div class="planning-visits">
 @forelse($coverages as $coverage)
 <article class="planning-visit" wire:key="coverage-{{ $coverage->id }}">
  <header><h4>{{ $coverage->equipment->name }}</h4><span>{{ $coverage->equipment->code }} · {{ $coverage->equipment->serial ?: 'Sin serie' }}</span></header>
  <p>{{ \App\Modules\MaintenancePlans\Configuration\Periods::label($coverage->first_month) }} — {{ \App\Modules\MaintenancePlans\Configuration\Periods::label($coverage->end_month) }}</p>
  @if($coverage->requirements)<p class="calendar-notes">{{ $coverage->requirements }}</p>@endif
  <p>{{ $coverage->completed }} / {{ $coverage->committed }} cumplidas · {{ max(0,$coverage->committed-$coverage->completed) }} pendientes totales</p>
  <p>{{ $coverage->programmed }} programadas · {{ max(0,$coverage->committed-$coverage->programmed) }} por programar · {{ $coverage->cancelled }} canceladas</p>
  @foreach($coverage->plans as $plan)<a class="planning-link" href="{{ route('operations.maintenance_plans',['record'=>$plan->id]) }}">{{ $plan->name }} · {{ \App\Modules\MaintenancePlans\Configuration\Schedule::label($plan) }}{{ !$plan->active ? ' · Inactivo' : '' }}</a>@endforeach
  @if($coverage->plans->isEmpty())<p>Sin planes visibles; todavía no hay compromisos calculables.</p>@endif
  <footer>@if($canManage && $contract->active)<button class="planning-secondary" type="button" wire:click="edit({{ $coverage->id }})">Editar cobertura</button>@endif
  @if($contract->active && \App\Modules\MaintenancePlans\Configuration\Permissions::allows('manage'))<a class="planning-link" href="{{ route('operations.maintenance_plans',['new_for_coverage'=>$coverage->id]) }}">Crear plan para este equipo</a>@endif
  <a class="planning-link" href="{{ route('operations.maintenance_calendar',['contractId'=>$contract->id,'origin'=>'contract']) }}">Ver calendario contractual</a></footer>
 </article>
 @empty<p class="planning-empty">Añade los equipos reales y define sus coberturas. No se crean equipos ni condiciones de ejemplo.</p>@endforelse
 </div>
 <p wire:loading.delay role="status">Actualizando cobertura…</p>
</section>
