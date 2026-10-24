# Actividades / hallazgos — v11.46.0

`/operations/activities` utiliza el mismo editor lateral, pestañas, tablas y vistas personales que Instituciones y Servicios. No añade tablas ni campos a la base de datos.

## Pestañas y flujo

- **General:** servicio relacionado y contexto de técnico, ticket y equipo según los permisos del usuario.
- **Actividad y resultado:** descripción obligatoria del trabajo/hallazgo y resultado opcional, con guardado automático.
- **Evidencias:** cargar, previsualizar, ampliar, ordenar y retirar fotografías privadas.

Desde Servicios → Actividades, **Nueva actividad para este servicio** abre un borrador con el vínculo preseleccionado. Primero se guardan el servicio y la descripción de la actividad; después se habilita la carga de fotografías. No se crea un registro vacío por abrir el borrador. Las fotos son opcionales.

Las vistas iniciales son Todas, Con resultado, Resultado pendiente y Con fotografías. Puedes configurar columnas, filtros, orden y agrupación de forma personal.

## Fotografías y seguridad

Se conservan los límites existentes: hasta cuatro imágenes PNG/JPEG/WebP de 1 MiB cada una. Los archivos se validan en servidor y se guardan en el disco privado, dentro de `service-photos/{usuario}/`. La vista previa utiliza una ruta autenticada y autorizada, sin URL pública. La respuesta impide almacenamiento en cachés compartidas (`private, no-store`).

Cada acción de evidencia verifica permiso de gestión, actividad seleccionada y sesión del editor. Una carga que termina después de cambiar o cerrar la ficha no se asocia a la actividad nueva. Las rutas de archivos guardadas y los datos de confirmación son propiedades bloqueadas de Livewire; no pueden modificarse desde el cliente.

La carga utiliza archivos temporales de Livewire. Solo se asocian al registro al completar una acción válida. Un fallo de guardado después de copiar un archivo puede dejarlo privado sin vínculo; no se añade una limpieza destructiva de archivos en esta entrega. No se crean respaldos automáticos.

Ordenar o retirar una fotografía guarda el cambio automáticamente y valida la versión del registro. Retirar exige confirmación mediante el diálogo compartido. Retirar significa desvincularla de esta actividad: el archivo original permanece privado, para no destruir referencias o evidencias anteriores. Ampliar abre la imagen autorizada en otra pestaña.

Usuarios de lectura pueden consultar las evidencias de actividades visibles, sin controles de carga, orden o retirada. Los técnicos solo ven actividades de servicios asignados. Las peticiones directas también aplican esas restricciones. Un conflicto de edición conserva los datos guardados y muestra el error; debe recargarse la ficha antes de reintentar.

El servicio relacionado continúa siendo editable según las reglas previas: debe ser visible y autorizado. El cambio de interfaz no introduce estados nuevos, aprobaciones ni una auditoría completa.

## Dónde modificar

| Archivo | Responsabilidad |
| --- | --- |
| Configuration/Fields.php | Campos normales y esquema Filament existente |
| Configuration/Tabs.php y Layout.php | Distribución visual y espaciado |
| Configuration/Columns.php y TableColumns.php | Vistas, columnas, filtros y agrupación |
| UI/ActivityPage.php y ActivityEditor.php | Ciclo de la ficha |
| UI/ManagesEvidence.php | Validación de carga, orden, retirada y sesión del editor |
| UI/Views/evidence.blade.php | Interacción de carga, progreso y galería |
| Queries/ActivityQuery.php | Consulta y caché por usuario |
| Queries/ActivityOptions.php | Selector de servicios y contexto autorizado |
| Actions/SaveActivity.php | Adaptador al guardado validado del flujo |
| Actions/PrivatePhotos.php | Validación común de archivos privados |
| Workflow/UI/Controllers/ActivityPhotoController.php | Entrega privada de imágenes |
| public/css/pages/activity-editor.css | Contexto, galería y adaptación móvil |
| tests/Feature/ActivityInterfaceTest.php | Comportamiento, permisos y evidencias |

Los cambios de actividad, servicio, ticket, equipo o usuario invalidan la caché de actividades. Los contadores de la ficha de Servicios se actualizan con los observadores existentes.

La siguiente entidad pendiente de adoptar esta interfaz es **Informes técnicos**. Firmas, aprobación, emisión final y catálogo de recomendaciones siguen pendientes.
