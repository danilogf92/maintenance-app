@foreach($receivedDetails['histories'] ?? [] as $entity=>$histories)
 <details class="record-editor-section"><summary>{{ \App\Modules\DataManagement\Configuration\Entities::label($entity) }} · {{ $histories->count() }}</summary>
  @php($column=$entity==='equipment_client_histories' ? 'client_id' : 'institution_id')
  @forelse($histories as $history)
   <div class="record-editor-hint"><strong>Referencia {{ $history->source_id }}</strong> · {{ $history->assigned_at ? ($history->date_only ? substr($history->assigned_at,0,10) : $history->assigned_at) : 'Sin fecha recibida' }} · {{ $history->$column ? 'Destino identificado' : 'Destino pendiente' }}
    @foreach($history->issues as $issue)<p>{{ \App\Modules\DataManagement\Configuration\HistoryIssues::message($issue) }}</p>@endforeach
   </div>
  @empty<p class="record-editor-hint">Todavía no hay historiales de este tipo cargados para el equipo. Cárgalos desde Admin → Gestión de datos → Carga de datos.</p>@endforelse
 </details>
@endforeach
