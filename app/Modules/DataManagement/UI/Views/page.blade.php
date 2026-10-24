<x-filament-panels::page>
 <link rel="stylesheet" href="{{ asset('css/pages/data-management.css') }}?v=11.64.0">
 <div class="dm">
  <div class="dm-intro"><div><h2>Preparar y revisar los datos</h2><p>Compara lo detectado en DataSanitized con las entidades y relaciones que ya existen en la aplicación.</p></div>
   @if($inventory)<x-filament::button color="gray" icon="heroicon-o-arrow-down-tray" wire:click="downloadReview" wire:loading.attr="disabled">Descargar diagnóstico</x-filament::button>@endif
  </div>
  <div class="dm-notice"><strong>Fase de revisión y carga controlada.</strong><p>Guardar una correspondencia no importa registros. La pestaña Carga de datos permite cargar clientes, centros e historiales y verificar los vínculos de los equipos.</p></div>
  @if($feedback)<div role="status" class="dm-feedback">{{ $feedback }}</div>@endif
  @if($errors->any())<div role="alert" class="dm-errors"><strong>Revisa lo siguiente:</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
  <nav class="dm-tabs" aria-label="Gestión de datos">
   @foreach(['overview'=>'Resumen','mappings'=>'Correspondencias y campos','relations'=>'Relaciones implementadas','imports'=>'Carga de datos','diagnostics'=>'Diagnóstico'] as $key=>$label)
    <button type="button" wire:click="selectTab('{{ $key }}')" class="{{ $tab===$key ? 'is-active' : '' }}" aria-current="{{ $tab===$key ? 'page' : 'false' }}">{{ $label }}</button>
   @endforeach
  </nav>
  @if($tab==='overview')@include('data-management::overview')
  @elseif($tab==='mappings')@include('data-management::mappings')
  @elseif($tab==='relations')@include('data-management::relations')
  @elseif($tab==='imports')@include('data-management::imports')
  @else @include('data-management::diagnostics')@endif
 </div>
</x-filament-panels::page>
