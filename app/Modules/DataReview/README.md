# Datos recibidos y revisión

Esta entrega usa importadores directos de los archivos recibidos. No introduce seeders de datos reales todavía. Los seeders de demostración solo pueden ejecutarse en pruebas automatizadas aisladas; el instalador deja de llamarlos.

## Preparación

`data:prepare` elimina únicamente demostraciones identificables por códigos DEMO/NAV-DEMO, cuentas con las direcciones reservadas generadas por los antiguos seeders y catálogos que provienen de equipos DEMO. Las cuentas SuperAdmin se preservan. Los catálogos importados se excluyen explícitamente de la eliminación. Las filas desconocidas, cuentas reales y organizaciones necesarias para mantener acceso no se borran por deducción.

`RemoveDemonstrationData` elimina primero informes/actividades/servicios/tickets, luego visitas/planes/equipos, contactos/clientes y catálogos sin uso. `RecordReferences` consulta las claves foráneas reales del esquema y bloquea cada eliminación que tenga una dependencia pendiente. No desactiva integridad referencial ni hace borrados en cascada sobre datos desconocidos. La limpieza de la base se ejecuta en una transacción. Los PDF temporales correspondientes a informes DEMO eliminados se retiran después de confirmar la transacción; no se borra el almacenamiento de documentos recibidos.

Un registro con código DEMO que ahora tiene relaciones no DEMO se conserva y se informa para revisión. La organización de un SuperAdmin puede conservarse por la misma razón. El resumen queda en el disco privado `data-review/cleanup.json`.

Los nombres reales contenidos en archivos recibidos no se reescriben por coincidencia textual con el nombre de un software o proveedor. Se retiran las etiquetas añadidas por la aplicación, no el contenido auténtico suministrado.

## Consulta y seguridad

`/operations/data-review` necesita sesión activa, permisos de equipos/tickets/servicios/marcas/modelos y `workflow.view_all`. Los técnicos sin acceso global no reciben totales ajenos. El enlace solo aparece cuando se cumplen esos permisos.

Los indicadores se calculan desde la base actual: no usan cantidades fijas para fingir completitud. Las bitácoras sin ticket en su archivo se distinguen de aquellas cuyo ticket referenciado todavía no se resolvió. No se impone un solicitante obligatorio. Los archivos faltantes se distinguen de archivos rechazados por SHA256/formato. Los modelos sin marca y los registros adicionales fuera de las importaciones se presentan para revisión.

Los importadores existentes permanecen compatibles internamente con sus identidades históricas para evitar duplicados. El instalador y la documentación de operación utilizan alias neutrales:

- `data:load-services`
- `data:load-catalogs`
- `data:load-tickets`
- `data:load-equipment` (después de catálogos y tickets)
- `data:load-engineers` (personas de servicio, sin crear cuentas)

La carga real ocurre después de `data:prepare`. Volver a actualizar no reintroduce las demostraciones, no borra datos recibidos y respeta vínculos ya verificados. Las fichas muestran Detalles y sus documentos, sin nombres del sistema anterior como etiquetas operativas.

## Vista previa PDF

`ReceivedPreviewData` selecciona una bitácora recibida visible para el usuario. Un perfil con visibilidad global puede usar el primer registro del archivo entregado si la carga en base aún no ocurrió. No permite eludir el ámbito de un técnico mediante el archivo fuente. Los datos que no se recibieron quedan vacíos; las referencias de actividades no se convierten en actividades ficticias. El número de filas solicitado por el antiguo control de crecimiento no crea repeticiones.

El adaptador `TemplateSampleData` mantiene compatibilidad de llamadas, pero devuelve exclusivamente ese contexto de datos reales o campos vacíos. No reutiliza el usuario conectado como técnico histórico ni asigna una institución distinta a la del servicio. No introduce fotos de equipo genéricas. Los nuevos PDF continúan siempre por la cola existente.


## Equipos (11.55.0)

Se incorporan 204 equipos. EquipmentReview muestra relaciones pendientes y contradictorias usando los datos actuales, incluidas referencias de tickets aún no recibidas y equipos citados por los catálogos que no llegaron en el archivo actual. Las marcas y modelos se resuelven por equivalencias de origen, no por IDs locales. Las relaciones de cliente e institución necesitan sus tablas de historial: no se usan esos números como si fueran clientes o centros médicos. Ver Equipment/Import/README.md para el resultado y los puntos de modificación.

La consulta de equipos adicionales fuera del archivo también se conserva para revisión. No se deduce que un registro manual sea falso. No se generan planes o visitas a partir de frecuencias en días, intervalos o indicadores internos del archivo. El calendario usa únicamente planes y visitas reales creados explícitamente.


## Ingenieros (11.56.0)

Se reciben ocho personas y se contrastan las referencias de asignaciones de los tickets con sus listas de visitas. EngineerReview distingue 624 tickets vinculados, uno sin asignación, 366 servicios con evidencia histórica y tres sin ingeniero verificable. Muestra 589 referencias de asignaciones que no aparecen en los tickets recibidos. Sigue pendiente la tabla FSE/Visitas, con fechas y relaciones. Las cuentas de acceso no se crean ni se vinculan automáticamente; los datos sensibles del directorio requieren permiso específico. Ver FieldEngineers/README.md.
