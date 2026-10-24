# Tickets — interfaz normal de usuarios

Ruta /operations/tickets, con el mismo formato que Instituciones, Usuarios, Clientes y Equipos. Admin conserva su panel independiente.

## Configuración

- Fields.php: etiquetas, tipos, estados y prioridades; make mantiene compatibilidad con el recurso Filament anterior.
- Tabs.php: General (asunto, descripción, prioridad, estado y apertura), Asignación (equipo y técnico), Servicios.
- Layout.php: márgenes, columnas, separación y altura de controles.
- Columns.php/TableColumns.php: columnas, vistas por estado, orden, filtros y agrupaciones.
- UI/Views: tabla, barra y campos. La ficha utiliza components/record-editor/tabbed-detail.blade.php.
- TicketPage hereda Workflow/UI/TabbedRecordPage para selección, guardado automático, reintentos y concurrencia.
- SaveTicket delega en SaveRecord y Rules. TicketQuery, TicketOptions y RelatedServices separan consultas.

## Uso

Pulsa +. Completa asunto, estado, prioridad, apertura, equipo y técnico. El técnico actual se preselecciona si puede recibir asignaciones. El código se genera al guardar; no hay botón Guardar. Un borrador incompleto puede cerrarse sin crear registros.

Desde Equipos → Tickets, Nuevo ticket para este equipo abre la ficha con el equipo preseleccionado y no inserta registros hasta completar los datos. Es una acción sujeta a tickets.manage y equipment.view.

El equipo tiene búsqueda por nombre, código y serie; el seleccionado se conserva aunque no figure entre los primeros 30 resultados. Las nuevas asignaciones solo aceptan técnicos activos autorizados. El técnico histórico permanece visible si fue desactivado y se pueden editar otros datos conservando esa asignación; no puede asignarse un técnico inactivo nuevo.

Si ya hay servicios se bloquean equipo y técnico, tanto en interfaz como en servidor. Estado, prioridad y descripción siguen editables. No se agregan automatizaciones entre estados de ticket y servicio.

Servicios carga al abrir la pestaña, con búsqueda y paginación. Solo muestra servicios del ticket que el usuario tiene permiso de consultar. El código abre la ficha actual del servicio; Servicios todavía no está rediseñado.

## Permisos y conservación

Se conservan workflow.access, tickets.view y tickets.manage. Técnico/Engineer consulta y gestiona tickets asignados y crea tickets para sí mismo; no puede consultar los de otro técnico. CEO consulta sin editar. Administradores gestionan según sus permisos. Las opciones de técnico se validan en servidor. services.view controla la pestaña y el contador de servicios; equipment.view controla las opciones de equipo.

La caché se separa por usuario/alcance y se invalida al cambiar tickets, equipos, técnicos o servicios. Si otra persona guardó antes, se rechaza el cambio; Descartar cambios y cerrar permite recargar. Usa Estado para conservar el historial; no se incorpora borrado.

No se agregan tablas ni migraciones para este cambio. Adjuntos del ticket, SLA, notificaciones, automatizaciones, contratos y migración Ninox siguen pendientes.
