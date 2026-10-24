# Planificación de mantenimiento

Menú normal → Planificación, `/operations/maintenance-plans`; Calendario, `/operations/maintenance-calendar`. Conserva las tablas completas, vistas, filtros, agrupación y editor lateral con pestañas. No incorpora paginación ni topes de filas del calendario.

## Planes independientes y contractuales

Un plan pertenece a un equipo. Puede ser independiente o vincularse a una cobertura de ese mismo equipo en un contrato. En Contratos → Equipos y cumplimiento se configura la cobertura individual; Crear plan para este equipo mantiene esa selección y vigencia.

Un contrato puede incluir equipos con uno, dos o tres años de cobertura y frecuencias distintas. La frecuencia se define por plan, no globalmente para el contrato. Cada equipo puede tener varios planes, por ejemplo preventivo y calibración. El plan contractual debe quedar dentro de la vigencia de su cobertura.

La referencia de contrato como texto se conserva para históricos o referencias externas; no crea relaciones. Los datos importados no se vinculan a contratos por similitud de texto.

## Frecuencias y precisión

- Meses y años: se eligen mes inicial y mes final opcional. Se conservan períodos completos sin asignar un día real. Enero con frecuencia de tres meses produce enero, abril, julio y octubre. Un año equivale a doce meses de calendario, no a 365 días.
- Días y semanas: se eligen fechas exactas de inicio/fin. Cada semana equivale a siete días. Varias visitas del mismo plan pueden coincidir en un mes, pero no en una misma fecha.
- `interval_value` y `interval_unit` contienen la frecuencia; `schedule_precision` identifica mes o día. Las columnas mensuales permanecen para compatibilidad, clasificación y calendario anual.
- La generación se ancla siempre al comienzo del plan. La aritmética de meses/años conserva el calendario y evita desbordes por años bisiestos.
- Los planes mensuales existentes mantienen sus períodos. Los planes históricos con frecuencia antigua no confirmada siguen bloqueados para generación hasta crear un nuevo plan y confirmar sus reglas. No se reactivan por inferencia.

Cantidad comprometida total permite limitar las visitas del plan durante toda su vigencia. Si queda vacía, en contratos se calcula por frecuencia y vigencia. Generar visitas no supera una cantidad explícita ni la fecha final. El panel permite programar hasta 120 visitas por operación para controlar cada generación; se puede repetir para añadir los siguientes períodos. Este límite no afecta a las filas consultadas o visibles.

## Uso

1. Crea el plan desde Planificación, desde el equipo o desde su cobertura contractual.
2. Completa General, Equipo y contrato y Programación. El guardado mantiene el funcionamiento automático del editor nativo.
3. En Visitas elige cuántos períodos añadir. No se crean tickets en este paso.
4. Una visita sin ticket puede cambiar de período, técnico, indicaciones o estado. Cancelar la edición conserva los valores persistidos.
5. Generar ticket exige técnico activo autorizado, plan y contrato activos y permiso de gestión de tickets. Es idempotente: repetir devuelve el mismo ticket. El solicitante sigue siendo opcional.
6. Registra los servicios. Una visita se cumple cuando su ticket tiene al menos un servicio Completado. Generar o resolver un ticket y aprobar un informe no equivalen a cumplir la visita.

Una visita cancelada no suma cumplimiento. Reabrir el único servicio completado retira ese cumplimiento. El cómputo se consulta en vivo y cuenta una vez cada visita, aunque existan varios servicios completados.

## Calendario

La vista Año conserva una fila por equipo y doce columnas mensuales. Mes, Semana y Día muestran una agenda del período seleccionado con fechas exactas cuando corresponden. Las visitas mensuales sin día confirmado se muestran como Sin día definido; en semana/día se incluyen las de los meses que intersectan el período, sin atribuirlas a ese día.

Se puede filtrar Todas, Por contrato o Independientes por equipo y seleccionar un contrato visible. La búsqueda por nombre, código o serie se conserva. La matriz incluye los equipos sin plan cuando no se aplica un filtro de origen/contrato. No se crean eventos a partir de meses vacíos.

Pulsa una visita para ver su detalle. La matriz permite arrastrar a otro mes de la misma fila. Para fechas exactas se conserva el número de día, limitado al último día del mes de destino; el formulario permite editar la fecha exacta. El movimiento no reasigna el equipo ni cambia la frecuencia de las visitas siguientes.

No se arrastran visitas canceladas, con ticket o de planes inactivos. El servidor valida también las llamadas directas, límites, duplicados y revisión vigente. El estado Cumplida se basa en servicios completados; Ticket resuelto muestra Servicio pendiente si todavía no existe uno completado.

## Historial y acceso

Un plan con visitas conserva equipo, contrato, vigencia, cantidad y frecuencia; se crea otro plan para cambiarlos. El técnico por defecto afecta únicamente a nuevas visitas. Inactivar no borra ni cancela silenciosamente el historial. Una visita con ticket se gestiona desde ese ticket.

La autorización existente de planificación y equipos se mantiene. Sin `workflow.view_all` se muestran planes creados por el usuario, asignados a él o con una visita suya. Los vínculos a tickets respetan sus permisos propios. Las consultas de calendario y cumplimiento usan relaciones y agregados por conjuntos, sin una consulta de servicios por tarjeta.

No se incorporan automatismos por fecha, cron, notificaciones, colas nuevas o generación automática de tickets. Los PDF mantienen su cola existente.

## Dónde cambiar

- Configuration/Fields, Tabs, Layout, Relations y Rules: editor y validación.
- Configuration/Schedule: unidades, recurrencia y cantidad por vigencia.
- Actions/SavePlan, GenerateVisits, SaveVisit y MoveCalendarVisit: integridad y programación.
- Configuration/CalendarStatus y relación MaintenanceVisit::completedServices: cumplimiento.
- Queries/CalendarQuery y UI/CalendarPage: filtros y calendario.
- UI/Views/calendar, agenda, calendar-card y calendar-detail: presentación.

El instalador no genera equipos, contratos o planes ficticios. Las pruebas usan exclusivamente datos aislados.
