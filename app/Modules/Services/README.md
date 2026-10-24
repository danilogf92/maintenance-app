# Servicios — interfaz normal v11.45.0

La ruta `/operations/services` utiliza el mismo editor lateral, tablas y controles que Instituciones. Admin permanece separado. No se añaden tablas ni campos para esta interfaz.

## Pestañas

- **General:** ticket, fecha y estado. El equipo relacionado y el técnico se muestran como contexto de solo lectura según permisos. El técnico se obtiene del ticket al crear el servicio; al editar se conserva el técnico histórico del servicio.
- **Intervención:** tipo de servicio, resumen y recomendaciones, una por línea. El tipo sigue siendo texto libre; su catálogo queda pendiente.
- **Actividades:** registros del servicio, resultado y cantidad de fotografías; cada enlace abre la actividad existente. Las fotos siguen siendo privadas y se consultan desde su ficha autorizada.
- **Informes:** código, título, revisión y estado de borrador. El enlace abre el informe existente, desde donde se solicita el PDF por cola y se abre su vista previa. Esta entrega no añade aprobación ni emisión final.

Actividades e Informes se cargan por separado al abrir su pestaña y tienen búsquedas y paginación propias. No se cargan snapshots de informes para construir la lista. Las pestañas y contadores se ocultan sin el permiso correspondiente. Las consultas verifican tanto el servicio padre como el módulo relacionado y el técnico autorizado.

Desde Tickets → Servicios puedes elegir **Nuevo servicio para este ticket**. La ruta `?new_for_ticket=ID` abre un borrador con ese ticket seleccionado, sin insertar hasta el primer guardado válido. `?record=ID` abre un servicio existente. Ambos parámetros se validan y autorizan.

## Dónde modificar cada parte

| Archivo | Responsabilidad |
| --- | --- |
| Configuration/Fields.php | Campos de la ficha normal y esquema Filament existente |
| Configuration/Tabs.php | Pestañas, orden y ubicación de campos |
| Configuration/Layout.php | Espaciado, columnas y tamaño de controles |
| Configuration/Columns.php y TableColumns.php | Columnas, vistas por estado, filtros, orden y agrupación |
| UI/ServicePage.php y ServiceEditor.php | Ciclo de la ficha y validación de configuración |
| UI/Views | Tabla, barra, campos y listas relacionadas |
| Actions/SaveService.php | Adaptador al guardado validado del flujo operativo |
| Queries/ServiceQuery.php | Tabla principal, alcance y caché por usuario |
| Queries/ServiceOptions.php | Búsqueda de tickets y contexto autorizado |
| UI/RelatedServiceRecords.php | Carga y búsqueda compartida de relaciones |
| UI/RelatedActivities.php y RelatedReports.php | Campos y vista de cada relación |
| public/css/pages/service-editor.css | Contexto visual de equipo y técnico |
| tests/Feature/ServiceInterfaceTest.php | Permisos, relaciones, concurrencia y guardado |

## Reglas conservadas

El guardado es automático, con aviso de campos pendientes o de conflicto. Los reintentos de creación no duplican el servicio. Cada edición valida `lock_version`. El ticket no se puede cambiar cuando el servicio tiene actividades o informes; no se elimina el historial. Las reglas también se aplican en servidor, aunque se manipule el formulario.

Las vistas personales mantienen filtros/columnas propios y no modifican los registros. Los cambios de servicio, ticket, equipo, usuario, actividad o informe invalidan la caché de servicios. Los técnicos solo consultan servicios asignados; el alcance no depende de lo que se muestre en la interfaz.

El componente compartido `Workflow/UI/TabbedRecordPage.php` ahora mantiene qué relaciones se cargaron en cada sesión del editor. `components/record-editor/tabbed-detail.blade.php` admite configuración individual para más de una relación. Clientes, Equipos y Tickets conservan su funcionamiento.

Siguiente entidad: **Actividades / hallazgos**, adoptando esta misma estructura visual y mejorando el manejo de evidencias.
