@props(['label', 'recordId' => null])
<span class="ops-mobile-panel-title">{{ $label }}<small>{{ $recordId ? '#'.$recordId : 'Nuevo registro' }}</small></span>
