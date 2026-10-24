@php($dailySchedule = in_array($interval_unit,['days','weeks']))
@if(!($dailySchedule && in_array($field,['first_month','end_month'])) && !(!$dailySchedule && in_array($field,['first_date','end_date'])))
@php($lockedSchedule = $hasVisits && in_array($field, ['equipment_id','contract_coverage_id','first_month','end_month','first_date','end_date','interval_unit','interval_value','expected_visits']))
<div class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}" wire:key="plan-field-{{ $field }}">
 <label for="plan-field-{{ $field }}">{{ $definition['label'] }}{{ !empty($definition['required']) ? ' *' : '' }}</label>
 @if($definition['type'] === 'equipment')
  @if($canManage && !$lockedSchedule)<input type="search" @input.stop @change.stop aria-label="Buscar equipo del plan" placeholder="Buscar nombre, código o serie…" wire:model.live.debounce.350ms="equipmentSearch">@endif
  <select id="plan-field-{{ $field }}" wire:model="equipment_id" @disabled(!$canManage || $lockedSchedule)><option value="">Selecciona un equipo…</option>@foreach($equipmentOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
 @elseif($definition['type'] === 'unit')
  <select id="plan-field-{{ $field }}" wire:model.live="interval_unit" @disabled(!$canManage || $lockedSchedule)>@foreach(\App\Modules\MaintenancePlans\Configuration\Schedule::units() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
 @elseif($definition['type'] === 'coverage')
  <select id="plan-field-{{ $field }}" wire:model="contract_coverage_id" @disabled(!$canManage || $lockedSchedule)><option value="">Independiente, sin contrato</option>@foreach($coverageOptions as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
  @if($contract_coverage_id && !isset($coverageOptions[$contract_coverage_id]))<option value="{{ $contract_coverage_id }}">Cobertura contractual vinculada</option>@endif</select>
  <small class="record-editor-hint">La referencia de texto no vincula un contrato. Selecciona una cobertura real.</small>
 @elseif($definition['type'] === 'technician')
  <select id="plan-field-{{ $field }}" wire:model="technician_id" @disabled(!$canManage)><option value="">Sin asignar todavía</option>@foreach($technicianOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
  <small class="record-editor-hint">Se aplica a las nuevas visitas. Las ya programadas se editan individualmente.</small>
 @elseif($definition['type'] === 'checkbox')<input id="plan-field-{{ $field }}" type="checkbox" wire:model="{{ $field }}" @disabled(!$canManage)>
 @elseif($definition['type'] === 'textarea')<textarea id="plan-field-{{ $field }}" wire:model="{{ $field }}" rows="4" maxlength="{{ $definition['max'] }}" @disabled(!$canManage)></textarea>
 @else<input id="plan-field-{{ $field }}" type="{{ $definition['type'] }}" wire:model="{{ $field }}" @disabled(!$canManage || $lockedSchedule) @if(isset($definition['max'])) maxlength="{{ $definition['max'] }}" @endif @if($definition['type'] === 'number') min="1" max="{{ $field === 'expected_visits' ? 100000 : 1440 }}" @endif>
 @endif
 @if($field === 'expected_visits')<small class="record-editor-hint">Límite total del plan, no por año. Si queda vacío, el compromiso contractual se calcula por frecuencia y vigencia.</small>@endif
 @if($lockedSchedule)<small class="record-editor-hint">La programación tiene historial; este dato se conserva.</small>@endif
 @error($field)<small role="alert">{{ $message }}</small>@enderror
</div>

@endif
