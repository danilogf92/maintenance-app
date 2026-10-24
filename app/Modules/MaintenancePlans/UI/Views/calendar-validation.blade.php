@if($errors->any())
<div class="calendar-error" role="alert"><strong>No se pudo actualizar la planificación:</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
@endif
