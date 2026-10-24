@if($ninoxDetails)
 <div role="status" class="record-editor-section">
  <h3>Datos del servicio</h3>
  <p>Los datos originales y archivos están disponibles en Detalles. Las relaciones pendientes todavía no forman parte de las pestañas Actividades e Informes.</p>
  <button type="button" class="ops-button" @click="selectTab('ninox')">Ver datos y archivos</button>
 </div>
@endif
