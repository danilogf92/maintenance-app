# Equipos recibidos — 11.55.0

## Carga

El bundle ejecuta `data:load-equipment` después de catálogos y tickets. El comando lee `database/imports/equipment/export/records.jsonl` completo y valida todas las filas antes de importar. `--dry-run` solo valida; `--source=` permite indicar otro JSONL compatible. No se ejecutan generadores de demostración. Los seeders de datos reales se definirán después de cuadrar las relaciones.

Se recibieron 204 equipos, sin fotografías, firmas ni PDF de equipo. Se conserva cada fila original en `received_equipment.payload`, cifrada con APP_KEY y oculta en serialización. La identidad única es source_table/source_id, no la serie ni el identificador local. El mismo serial puede repetirse: el archivo tiene tres N/A.

El código EQ-REC-ID permite identificar el registro sin atribuir un código inexistente al origen. El nombre de pantalla usa el componente principal si existe, luego el modelo verificado y por último una referencia explícita al registro. Los valores originales están en Equipos → Detalles; no se sobrescriben con estos rótulos de pantalla.

## Relaciones

- Las 204 marcas se vinculan por su equivalencia de identidad importada. No se interpretan los números de origen como IDs locales.
- 200 equipos se vinculan con modelos; cuatro referencian el modelo 106, ausente del archivo anterior. No se crea un modelo solo a partir de ese número.
- Las referencias directas confirman la marca de 48 modelos que antes se había deducido por equipos comunes. No se reasigna un modelo cuyo vínculo no coincide. El contenido original del catálogo permanece intacto.
- 622 tickets coinciden en ambas direcciones y se vinculan. El ticket de referencia 2030 apunta a equipo 337, pero aparece en las listas de 53 y 337: queda sin equipo para revisión. Otros dos tickets no tenían referencia de equipo.
- Los equipos incluyen referencias a 24 tickets que no llegaron en el archivo de Tickets anterior. Se muestran como pendientes, sin crear tickets vacíos.
- Los catálogos anteriores mencionan seis referencias de equipos ausentes de esta exportación: 8, 13, 71, 123, 293 y 378. Se muestran para verificar si son históricos o falta exportarlos; no se borran sus referencias.
- Cliente e institución quedan nulos: los campos recibidos son historiales, no referencias directas a esas entidades. Se necesitan Histórico de Centros Médicos Asociados, Histórico de Clientes para Cobro y las tablas de Instituciones/Clientes, con sus referencias y fechas, para determinar la relación actual.
- Técnicos, contactos, asesores, contratos, cronogramas, componentes y otros historiales no se convierten en entidades por inferencia.

## Consulta y planificación

Los equipos recibidos permanecen en consulta hasta verificar sus relaciones. El backend también impide su edición por la acción genérica. Se mantiene la interfaz con pestañas y se añade Detalles con secciones desplegables. El estado es Por verificar: el archivo no permite afirmar que el equipo está operativo. Propietario informativo Usuario/Corpomedica no equivale al cliente para cobro.

Las fechas de fabricación, último mantenimiento, valores económicos, frecuencias y porcentajes se muestran en su escala original. No se normaliza 0.5 a 50 ni se interpretan intervalos sin unidad confirmada. Las visitas anuales recibidas (1 Visita/2 Visitas/Manual) no generan planes automáticamente. No se ejecuta ningún indicador de desarrollo incluido en los datos.

El calendario muestra los 204 equipos reales; no aparecen visitas hasta que se creen planes y se programen sus meses explícitamente o se importen cronogramas verificados. Se conserva el flujo mensual. Los tickets históricos siguen en consulta y sin técnico supuesto. Emitir un informe operativo requiere equipo, cliente, institución y técnico verificados; faltantes producen validación, no un error de servidor. La vista previa del diseñador puede mostrar los datos de equipo ya vinculados a una bitácora visible.

## Seguridad y repetición

Los importadores tienen un bloqueo de ejecución y transacciones por registro. La repetición con el mismo origen conserva valores, revisiones y archivos; cambios en el origen o códigos ocupados se reportan como conflictos. Los vínculos existentes no se sustituyen. Las contradicciones de referencia son pendientes de revisión y no detienen la instalación; una identidad de equipo conflictiva sí provoca error para evitar sobrescritura.

La migración solo hace opcionales las claves de cliente e institución para conservar referencias desconocidas, manteniendo sus claves foráneas. Los equipos nuevos manuales siguen exigiendo ambas relaciones. No hay reversión automática de datos. La migración rechaza bajar si existen equipos recibidos o relaciones pendientes.

Datos por completar requiere los permisos existentes de consulta de equipos, catálogos, tickets y servicios, además de visibilidad global. La ficha usa equipment.view; la modificación y planificación conservan sus permisos en servidor. No hay rutas públicas para estos datos. APP_KEY debe conservarse para leer las fuentes cifradas.

## Archivos a modificar

- Import/EquipmentSource.php: validaciones del archivo completo.
- Import/ImportEquipment.php: valores iniciales y correspondencias.
- Import/TicketEquipmentEvidence.php y LinkEquipmentTickets.php: evidencia y enlaces, sin sobrescrituras.
- Import/ConfirmCatalogLinks.php: confirmación del vínculo modelo/marca.
- Configuration/ReceivedFields.php y UI/Views/received.blade.php: campos y secciones de consulta.
- DataReview/Queries/EquipmentReview.php y resources/views/data-review/equipment.blade.php: pendientes dinámicos.

No se ha añadido paginación, reducido filas de listas ni creado respaldos automáticos. La actualización mantiene colas y vista previa PDF existentes.
