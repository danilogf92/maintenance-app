<div class="data-review-page"><link rel="stylesheet" href="{{ asset('css/pages/data-review.css') }}?v=11.56.0"><div class="data-review-content">
 <h1 class="text-2xl font-bold">Datos por completar</h1>
 <p class="my-4">Trabajamos con los archivos recibidos. Las referencias sin resolver permanecen pendientes; no se crean entidades ficticias.</p>
 <h2 class="text-lg font-semibold">Datos disponibles</h2>
 <table class="record-related-table"><thead><tr><th>Entidad</th><th>Registros recibidos</th></tr></thead><tbody>
 @foreach($received as $label=>$count)<tr><td>{{ $label }}</td><td>{{ $count }}</td></tr>@endforeach
 </tbody></table>
 <h2 class="my-4 text-lg font-semibold">Qué subir a continuación</h2>
 <ol class="list-decimal space-y-3 pl-6">
  <li><strong>Histórico de Centros Médicos Asociados y Histórico de Clientes para Cobro</strong>. Los equipos traen referencias a esos historiales, no los identificadores directos de la ubicación y cliente actuales.
   @if(($received['Clientes'] ?? 0)===0 || ($received['Centros médicos'] ?? 0)===0)Carga primero Clientes y Centros médicos desde Admin → Gestión de datos → Carga de datos.
   @elseif(($received['Historiales de clientes'] ?? 0)>0 && ($received['Historiales de centros'] ?? 0)>0)Ya se conservan los historiales QC/RC. Revisa en Admin los vínculos resueltos y los casos pendientes.
   @elseCarga las tablas QC · Centro Asociado y RC · Clientes Asociados desde Admin → Gestión de datos → Carga de datos.@endif
  </li>
  <li><strong>Catálogo de Modelos actualizado</strong>. Permite completar las referencias pendientes indicadas en la revisión de equipos.</li>

  <li><strong>Asignaciones FSE / Visitas</strong>. Los ingenieros ya están recibidos. Falta el detalle de las asignaciones para revisar fechas, cambios y referencias adicionales; no son las visitas mensuales del calendario.</li>
  <li><strong>Actividades / hallazgos y Recomendaciones</strong>. Para completar el contenido de cada servicio.</li>
  <li><strong>Firmas de solicitantes y Contactos</strong>. Para comprobar las firmas y contactos en sitio. El solicitante del ticket sigue siendo opcional.</li>
  <li><strong>Componentes, Repuestos instalados y Documentación de Equipo</strong>.</li>
  <li><strong>Proveedores, Contratos y Cronogramas</strong>. Para completar relaciones comerciales y planificación mensual con datos reales.</li>
 </ol>
 @include('data-review.protocols')
 @include('data-review.equipment')
 @include('data-review.engineers')
 <h2 class="my-4 text-lg font-semibold">Registros adicionales por verificar</h2>
 <p>Estos registros no están identificados en los archivos recibidos. Se conservan para no confundir datos reales creados manualmente con demostraciones.</p>
 <ul>@foreach(['equipment'=>'Equipos','tickets'=>'Tickets','services'=>'Servicios','brands'=>'Marcas','equipment_models'=>'Modelos'] as $key=>$label)<li>{{ $label }}: {{ $additional[$key] }}</li>@endforeach</ul>
 <h2 class="my-4 text-lg font-semibold">Revisión de relaciones y archivos</h2>
 <p>Bitácoras con ticket pendiente: {{ $pendingServices }}. Bitácoras sin ticket en el archivo recibido: {{ $withoutSourceTicket }} (no se inventará uno).</p>
 <p>Documentos pendientes: {{ $missingDocuments }}. Archivos rechazados por integridad o formato: {{ $invalidDocuments }}.</p>
 <p>Modelos sin marca verificada: {{ $models->count() }}.</p>
 <ul class="my-4 space-y-2">@foreach($models as $model)<li><a href="{{ route('operations.equipment_models',['record'=>$model->id]) }}">{{ $model->name }}</a></li>@endforeach</ul>
 @if($protected)<p role="status">{{ $protected }} registros de prueba o sus organizaciones se conservaron porque tienen relaciones protegidas o pertenecen al acceso administrativo. Requieren revisión antes de eliminarlos.</p>@endif
 <p class="my-4">Entrega cada exportación con registros, identificadores y relaciones. Los adjuntos pesados pueden permanecer en tu carpeta local; indica su ruta.</p>
</div>
</div>
