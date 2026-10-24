@if($ninoxDetails)
 <section class="record-editor-section" role="status">
  <h3>Datos del ticket</h3>
  <p>Estado original: {{ $ninoxDetails['source']['status'] }}. Equipos y técnicos pendientes de verificar. La exportación contiene fecha, sin hora ni prioridad.</p>
  <button type="button" class="ops-button" @click="selectTab('ninox')">Ver datos y bitácoras</button>
 </section>
@endif
