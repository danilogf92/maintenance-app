# Historiales de clientes y centros de equipos

## Operación

Instala v11.64.0 con Actualizar.cmd. El actualizador intenta preparar ambos orígenes mediante `data/PrepararHistoriales.ps1`. Usa la misma configuración INI y el único archivo de correspondencias ya existentes.

En Admin → Gestión de datos → Carga de datos pulsa **Cargar historiales y vincular equipos**. El proceso es independiente de la carga anterior de clientes/centros; primero deben existir esos registros y los equipos recibidos.

Para repetir la lectura de archivos cambiados, ejecuta `data\PrepararHistoriales.cmd`. Preparar solo produce una copia de trabajo privada y un ACK con imported=false. El botón de Admin es el que programa la importación en cola.

## Estructura y correspondencias

| Origen | Campo | Destino / significado |
| --- | --- | --- |
| QC · Centro_Asociado | equipment | ID de origen C, resuelto mediante received_equipment |
| QC | centers | ID de origen B, resuelto mediante data_source_records |
| QC | assigned_date | equipment_institution_histories.assigned_at |
| RC · Clientes_Asociados | equipments | ID de origen C |
| RC | clients | ID de origen A |
| RC | assignation_datr | equipment_client_histories.assigned_at; se conserva la grafía del exportador |
| C · Equipos | equipment_medical_centers_history | Referencias a filas QC; no a centros B |
| C | customer_history | Referencias a filas RC; no a clientes A |

Tablas nuevas: `equipment_client_histories`, `equipment_institution_histories`. Ambas conservan source_id, source_hash, payload cifrado, equipo y destino local (nullable), fecha validada y códigos de observaciones. Los originales no se sobrescriben si cambia su hash.

`data_import_runs.operation` distingue catalogs de histories. `link_results` conserva el resultado de asignación de cada equipo en esa ejecución. Los datos originales y notas no se incluyen en los diagnósticos descargables.

## Archivos principales

- `Configuration/EquipmentHistories.php`: campos por origen, tabla e IDs recíprocos.
- `Support/HistoryValue.php`: referencias simples/listas y fechas ISO; no convierte IDs por coincidencia numérica.
- `Import/EquipmentHistoryRecord.php`: conservación cifrada y validación de referencias; puede completar referencias pendientes en una repetición con el mismo origen.
- `Actions/ResolveEquipmentAssignments.php`: resolución por equipo, fecha y destino; conserva vínculos actuales diferentes.
- `Actions/StartEquipmentHistoryImport.php`: preparación de correspondencias faltantes y envío a cola bajo data_management.manage.
- `Jobs/ImportEquipmentHistoryPackage.php`: validación del solicitante, transacción, resultados y caché.
- `Support/EquipmentHistoryPackage.php`: perfil QC/RC del lector común de paquetes.
- `data/scripts/CatalogRecords.ps1`: lector compartido JSONL/JSON, preferencia de exportación y validación de identidades.
- `data/PrepararHistoriales.ps1`: origen de Windows → copia privada visible desde Docker. No copia PDF ni imágenes.

Los nombres anteriores de clases PHP se encuentran en `app/Modules/DataManagement/`.

## Reglas de resolución

1. Se resuelven primero las identidades de origen contra sus mapas locales.
2. Deben coincidir las referencias del equipo y las filas del paquete, sin filas faltantes ni nuevas afirmaciones no confirmadas por el equipo.
3. Cualquier conflicto de origen, destino ausente o referencia contradictoria bloquea la selección de esa relación.
4. Se excluyen asignaciones futuras. Fechas sin hora se comparan como fechas de calendario de Ecuador; horas ISO se normalizan a America/Guayaquil.
5. Un único destino activo se admite aun cuando falte una fecha. Con destinos distintos se necesitan todas las fechas activas y una fecha máxima inequívoca.
6. Una fecha sin hora y otra con hora el mismo día no ordenan destinos diferentes de forma segura. Quedan pendientes. Dos horas precisas sí permiten comparar el instante.
7. Solo se completa un vínculo actual vacío. Si ya coincide, se marca existente. Si difiere, se informa conflicto y no se reasigna.
8. No hay reasignaciones programadas: para evaluar una asignación futura cuando corresponda, se repite la carga.

No se selecciona por el ID más alto. No se reconstruyen registros ausentes ni se crean cuentas de usuario. Un origen actualizado que contradiga el ya conservado necesita revisión y no se reemplaza automáticamente en esta fase.

## Consulta y diagnósticos

Equipos → Detalles muestra el estado de cliente/centro y los dos historiales colapsables, con fechas, IDs de origen y observaciones. El estado operativo del equipo y la edición de históricos permanecen en consulta.

Cada ejecución escribe en `storage/app/private/data-management/imports/<run-id>/`:

- status.json: operación, estado, filas creadas/actualizadas y relaciones vinculadas.
- errors.json: códigos e IDs de origen, sin nombres, RUC ni correos.
- ack.json: resultado y necesidad de revisión de relaciones.
- assignment-results.json: resultado congelado por equipo y relación, con IDs de los historiales utilizados.

El ZIP añade pending-links.json con el estado actual de la base, que puede diferir del resultado congelado de una ejecución anterior.

Si se produce un fallo inesperado durante la escritura, se revierte la transacción de esa ejecución. Se conserva el estado fallido y su ACK. Los rechazos o ambigüedades esperados se reportan y no impiden conservar los otros historiales válidos.

## Pendientes

Resolución manual de conflictos, corrección de centros sin nombre válido, contactos R y cronograma AC. El Update destructivo, Erase y Seed globales no forman parte de este cambio.
