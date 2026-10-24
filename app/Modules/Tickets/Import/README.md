# Tickets Ninox — v11.52.2

## Datos recibidos

625 tickets de tabla F, entre 2022-12-01 y 2026-09-22. El código original se conserva (todos son únicos). La exportación contiene 29 campos y ninguna imagen de ticket (`value_19` está vacío en los 625 registros). El JSONL e import-plan.json se incluyen en database/imports/ninox-tickets. Si una exportación futura aporta imágenes, la validación pedirá implementar su importación: no las omite silenciosamente.

Los 625 registros se importan a `tickets`. `ninox_ticket_imports` conserva tabla/ID/hash y payload íntegro cifrado con APP_KEY, sin cambiar los IDs originales por IDs de Laravel. No cambiar APP_KEY. Asunto y datos originales no se recortan: el motivo más largo tiene 211 caracteres y cabe en la columna subject existente (255). El formulario de tickets nuevos mantiene su límite y validaciones habituales.

## Relaciones verificadas

366 de las 369 bitácoras recibidas tienen ticket asociado y los 366 vínculos coinciden en ambas exportaciones: `K.tickets_associated` apunta a `F.ninox_id` y `F.value_15` incluye `K.ninox_id`. Se vinculan con los IDs reales que Laravel asignó al importar, nunca con la igualdad casual de IDs numéricos.

Las bitácoras 1981, 2133 y 2145 no tienen referencia de ticket y permanecen sin él. Un ticket puede tener varias bitácoras. No se crean servicios vacíos para otras referencias que todavía no se recibieron.

623 tickets referencian 156 equipos Ninox distintos; dos no tienen equipo de origen. **No se crean equipos ficticios**: equipment_id queda pendiente hasta recibir Equipos y sus relaciones con clientes e instituciones. Las asignaciones FSE son referencias a registros de asignación; no son necesariamente IDs de usuarios. Se conservan y no crean cuentas ni asignan técnicos por coincidencia de números.

`contact_on_site` es contacto en sitio, no prueba de quién solicitó o reportó el ticket. Se conserva como referencia pendiente, sin copiarlo automáticamente a requester_contact_id. Solicitar el ticket sigue siendo opcional en el flujo normal.

Cronogramas de contrato, observaciones/decisiones, facturas y tareas quedan conservados como referencias originales; requieren sus tablas. No se genera planificación a partir de IDs de cronograma ni fechas aisladas. La planificación actual sigue por mes/año.

## Estados, fecha y prioridad

Los estados exactos del origen se conservan en `tickets.ninox_status` y en el payload. Ejemplos: Control de Calidad (502), Esperando instrucciones Comerciales (30), Requiere Nueva Visita (22), Verificado y Cerrado (2). No se interpreta Control de Calidad como Resuelto.

Los tickets importados se marcan `status=historical`, `origin=legacy`, `priority=unknown` (No informada en Ninox). No se atribuye prioridad normal ni un solicitante inexistente. `ticket_date` solo tiene fecha: opened_at usa medianoche como representación interna y la ficha Origen Ninox muestra explícitamente la fecha original sin hora. No es una hora de apertura confirmada.

`equipment_id` y `technician_id` admiten null para historia importada. Los formularios normales siguen exigiendo ambos. Los tickets históricos son de consulta; los cambios y la creación de nuevos servicios desde ellos se bloquean en servidor hasta verificar relaciones y adaptar el flujo. Las bitácoras históricas ya importadas sí se vinculan mediante el importador autorizado.

## Actualización y consulta

El mismo `Actualizar.cmd RUTA_DEL_PROYECTO` aplica la migración, importa bitácoras y catálogos anteriores, importa tickets y vincula las bitácoras. No hay copias manuales, respaldos automáticos ni borrado de datos.

Menú **Tickets → Histórico importado → seleccionar ticket → Origen Ninox**. Muestra estado exacto, motivo, información técnica, centro registrado, referencias pendientes y enlaces a las bitácoras visibles. General incluye un botón para abrir esta pestaña. En Servicios → Origen Ninox aparece el enlace inverso **Abrir ticket**. La columna configurable Estado en Ninox permite filtrar/ordenar por el texto original. Se conserva el listado completo; no se añade paginación.

La visibilidad respeta las reglas actuales: perfiles con workflow.view_all pueden consultar los importados; un técnico solo ve tickets/servicios asignados a su cuenta. Hasta verificar asignaciones, los importados sin técnico no se muestran como propios de todos los técnicos.

## Repetición, conflictos y código

La identidad única (tabla F, ID) y el código original evitan duplicados. Repetir la importación no reescribe tickets, datos originales ni vínculos existentes, y no incrementa versiones cuando el vínculo ya existe. Repetir el importador de bitácoras tampoco elimina estos vínculos.

Si cambia el origen, colisiona un código, falta la reciprocidad o una bitácora ya tiene otro ticket vinculado, se informa conflicto y no se sobrescribe. Cada registro/vínculo se guarda en transacción con bloqueo. El comando impide dos importaciones simultáneas de tickets. No se envían correos, generan PDF ni se crean usuarios durante la importación. Los PDF existentes de bitácoras mantienen su almacenamiento privado; los nuevos PDF siguen por cola.

```bash
php artisan ninox:import-tickets --dry-run
php artisan ninox:import-tickets
```

Comandos opcionales de diagnóstico; el actualizador ya ejecuta la importación real. Código PHP/Laravel, sin Python para importar. La migración permite limpiar esquemas vacíos de prueba; rechaza revertir con tickets importados o relaciones pendientes. El actualizador no ejecuta reversión automática de datos.

- TicketSource: valida identidad, código, fechas, referencias y ausencia de adjuntos no soportados.
- ImportTicket: identidad, payload cifrado y campos operativos conservadores.
- LinkNinoxServices: verifica referencias recíprocas y conserva vínculos previos.
- NinoxTicketDetails y UI/Views/ninox.blade.php: consulta y enlaces autorizados.
- SaveRecord::protectImported: protección de escritura de históricos.

Siguiente exportación necesaria: **Equipos**, después Clientes/Centros Médicos, técnicos y asignaciones FSE para resolver las relaciones todavía pendientes.
