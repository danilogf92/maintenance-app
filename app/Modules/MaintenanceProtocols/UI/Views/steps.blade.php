@if($protocolDetails)
 <p class="record-editor-hint">{{ $protocolDetails->steps->count() }} pasos. Se conserva el orden del protocolo recibido.</p>
 @foreach($protocolDetails->steps as $step)
  @include('maintenance-protocols::step', ['first' => $loop->first])
 @endforeach
@endif
