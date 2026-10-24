@if($engineerDetails)
<p class="record-editor-hint">Solo aparecen tickets y servicios que puedes consultar. Las cuentas de acceso y sus permisos se gestionan en Usuarios.</p>
@foreach(['tickets'=>'Tickets vinculados','services'=>'Servicios vinculados'] as $module=>$label)
<details class="record-editor-section"><summary>{{ $label }} · {{ $engineerDetails[$module]->count() }}</summary>
<ul>@forelse($engineerDetails[$module] as $record)<li><a href="{{ route('operations.'.$module,['record'=>$record->id]) }}">{{ $record->code }}</a></li>@empty<li>Sin registros visibles.</li>@endforelse</ul></details>
@endforeach
@endif
