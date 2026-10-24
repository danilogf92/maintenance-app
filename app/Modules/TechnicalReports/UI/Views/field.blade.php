<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="report-field-{{ $field }}">
 @php($inputId = 'report-field-'.$field)
 <label for="{{ $inputId }}">{{ $definition['label'] }} *</label>
 @if($field === 'service_id')
  @if($canManage)<input type="search" @input.stop @change.stop aria-label="Buscar servicio del informe" placeholder="Buscar código o tipo…" wire:model.live.debounce.350ms="serviceSearch">@endif
  <select id="{{ $inputId }}" wire:model="service_id" required @disabled(!$canManage)><option value="">Seleccionar...</option>
   @foreach($serviceOptions as $id => $label)<option value="{{ $id }}" @selected((string)$service_id === (string)$id)>{{ $label }}</option>@endforeach
  </select>
  @if($reportContext)
  <dl class="report-context">
   <div><dt>Técnico</dt><dd>{{ $reportContext['technician']?->name ?: 'Sin técnico disponible' }}</dd></div>
   @if($reportContext['ticket'])<div><dt>Ticket</dt><dd>{{ $reportContext['ticket']->code }}</dd></div>@endif
   @if($reportContext['equipment'])<div><dt>Equipo</dt><dd>{{ $reportContext['equipment']->code }} · {{ $reportContext['equipment']->name }}</dd></div>@endif
  </dl>
  @endif
 @elseif($field === 'report_template_id')
  <select id="{{ $inputId }}" wire:model="report_template_id" required @disabled(!$canManage)><option value="">Seleccionar...</option>
   @foreach($templateOptions as $id => $label)<option value="{{ $id }}" @selected((string)$report_template_id === (string)$id)>{{ $label }}</option>@endforeach
  </select>
  <small class="record-editor-hint">Se usa la versión publicada de la plantilla. Si no existe, se captura su diseño en borrador. Los logos y variables se resuelven con los datos del sistema.</small>
  <p class="record-editor-hint">Modificar la plantilla no cambia los informes guardados. Usa «Actualizar datos» en Revisión para capturar una nueva versión.</p>
 @else
  <input id="{{ $inputId }}" type="text" wire:model="title" maxlength="160" required @disabled(!$canManage)>
 @endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>
