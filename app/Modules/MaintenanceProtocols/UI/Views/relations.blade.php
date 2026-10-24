@if($protocolDetails)
 <section class="record-editor-section"><h3>Relaciones recibidas</h3>
 <p>Marca / modelo informativo: {{ $protocolDetails->model_info ?: 'Sin dato' }}</p>
 <p>Selección de modelo recibida: {{ $protocolDetails->source_payload['model_options'] ?? 'Sin dato' }}</p>
 <p class="record-editor-hint">Estos textos no confirman una relación con el catálogo de modelos.</p>
 <h4>Referencias de componentes</h4>
 <p style="overflow-wrap:anywhere">{{ implode(', ', $protocolDetails->source_payload['components'] ?? []) ?: 'Sin referencias' }}</p>
 <p class="record-editor-hint">Pendientes de importar y verificar los componentes. No se asignan a equipos por coincidencia de nombre.</p>
 </section>
@endif
