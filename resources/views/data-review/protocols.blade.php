@if($protocolReview)
 <h2 class="my-4 text-lg font-semibold">Protocolos de mantenimiento</h2>
 <p>{{ $protocolReview['protocols'] }} protocolos, {{ $protocolReview['steps'] }} pasos y {{ $protocolReview['files'] }} archivos de referencia recibidos.</p>
 <p>{{ $protocolReview['withoutParent'] }} pasos pendientes: {{ $protocolReview['withoutReference'] }} sin referencia de protocolo; los restantes esperan los protocolos de origen {{ implode(', ', $protocolReview['parents']) ?: 'ninguno' }}.</p>
 <p><a href="{{ route('operations.maintenance_protocols') }}">Abrir Protocolos → Pasos pendientes</a></p>
 <ul class="my-4 space-y-2">
 @foreach($protocolReview['emptyInstructions'] as $step)
  <li>Completar instrucciones o texto del paso de origen {{ $step->source_id }} del protocolo {{ $step->protocol->name }} (referencia {{ $step->protocol->source_id }}). Su aplicación está bloqueada hasta recibir ese contenido.</li>
 @endforeach
 </ul>
 <p>Para completar las relaciones faltan los componentes y la correspondencia verificable con los modelos. Los textos de marca/modelo se conservan como información.</p>
@endif
