# Bitácoras Ninox → Servicios

## Datos incluidos

Exportación original tabla K: 369 bitácoras, 2024-11-06 a 2026-09-29; 332 firmas PNG; 34 referencias de PDF. El bundle contiene los registros y firmas autorizados, por lo que debe tratarse como información privada. No contiene credenciales ni bases de datos. No publica los adjuntos en public/storage.

`database/imports/ninox-service-logs/export/records.jsonl` conserva los datos originales. `import-plan.json` documenta los 73 campos de origen; sus nombres propuestos no crean tablas ni columnas automáticamente.

Cada bitácora se importa como una fila de `services`, con código original, fecha real, servicio realizado y motivo de visita. Estado `historical`, sin atribuir aprobación ni finalización inexistentes. `ninox_service_imports` conserva tabla/ID/hash y los 73 campos originales en un payload cifrado con APP_KEY. No se debe cambiar APP_KEY. Los adjuntos quedan en `service_documents`, disco local privado. Las firmas son las originales de cada bitácora; nunca se convierten en firmas reutilizables del usuario.

## Un comando en Windows

`Actualizar.cmd "RUTA_DEL_PROYECTO"` aplica migraciones, prepara los PDF locales e importa los datos automáticamente. No borra registros ni crea respaldos. El origen predeterminado es el proyecto hermano `ninox-app/storage/app/ninox/files/2 Bitácora Servicio/Bitácora_Servicio/DataSanitizedBasic`. Es la carpeta que contiene `files/report_pdf`. El script resuelve la ruta a partir del destino de Maintenance APP; no depende del directorio desde el que se ejecute.

Solo copia los 34 PDF referenciados y verifica SHA256; no modifica los originales de Ninox. Si un archivo no existe, su ficha queda pendiente y la actualización continúa. Si está alterado o supera 100 MiB, se informa el error. Para otra ubicación se puede definir `NINOX_SERVICE_FILES_ROOT` antes de ejecutar el mismo actualizador. Los PDF no referenciados en el JSONL requieren un inventario posterior, no se asignan por coincidencia de nombres.

## Consulta

Menú Servicios → seleccionar número de bitácora → **Origen Ninox**. Muestra datos originales, técnico registrado como texto, cliente/institución originales, estado final del equipo, referencias pendientes y botones para abrir firmas y PDF disponibles en otra pestaña. Se conserva el diseño de fichas con pestañas. No se añade paginación.

Las bitácoras históricas quedan en consulta: edición y creación de un nuevo informe desde ellas se rechazan en servidor. Esto evita modificar datos originales o regenerar un documento incompleto. Los servicios nuevos conservan su flujo habitual. El PDF histórico es un adjunto original; no es una generación temporal de la cola de informes.

## Relaciones y equivalencias

Hay 366 bitácoras con referencia a 354 tickets distintos; tres sin ticket: 1981, 2133, 2145. No se inventan tickets ni usuarios. `services.ticket_id` y `technician_id` admiten null para conservar estas importaciones. Los formularios habituales siguen exigiendo ticket y su técnico como antes.

Los IDs de Ninox no se usan como IDs de Laravel. Sin equivalencias verificadas, quedan pendientes todos los vínculos de tickets y técnicos. El perfil con `workflow.view_all` puede consultar las importaciones; un técnico solo ve servicios asignados a su cuenta, como antes. Los adjuntos aplican el mismo ámbito de acceso, autenticación y control de estado activo.

Cuando se disponga de las otras exportaciones, se podrá usar un JSON verificado por el administrador:

```json
{"tickets":{"ID_NINOX":123},"technicians":{"Nombre exacto de engineer_assigned":45}}
```

Los números de destino son ejemplos: reemplazarlos por IDs verificados de esta instalación. No inferir correspondencia por igualdad de números ni crear cuentas basándose en un nombre.

```bash
php artisan ninox:import-service-logs --map=/ruta/equivalencias.json
```

Los destinos deben existir. Un vínculo resuelto nunca se reemplaza por otro: se reporta conflicto. La asignación de tickets no asigna al técnico automáticamente porque el técnico histórico puede diferir del actual.

Actividades/hallazgos (5.140 referencias), firmas de solicitantes (770), recomendaciones (164) y componentes (1.813) se conservan como referencias originales. No se crean registros de actividad con solo un ID ni se presentan como datos ya migrados. Falta recibir sus exportaciones, además de Tickets/Equipos para verificar la cadena de relaciones. El estado final del equipo se conserva como dato histórico; no modifica el estado actual de un equipo.

## Repetición y diagnóstico

La identidad única `(ninox_table, ninox_id)` evita duplicados; cada documento es único por servicio/tipo/SHA256. La repetición completa documentos faltantes y relaciones explícitas nuevas, sin modificar el payload original. Si cambia el origen o colisiona un código existente, reporta conflicto. Existe bloqueo de archivo para impedir dos ejecuciones simultáneas del comando. Cada bitácora se guarda en una transacción; no existe reversión automática de datos.

```bash
php artisan ninox:import-service-logs --dry-run
php artisan ninox:import-service-logs --files-root=/carpeta/DataSanitizedBasic
```

El segundo comando busca también las firmas incluidas y el inbox privado preparado por Windows. Un resumen de cantidades queda en `storage/app/private/ninox-service-import-summary.json`. El comando devuelve error si hay conflictos o adjuntos inválidos; los archivos ausentes son pendientes, no fallos fatales. El código usa PHP/Laravel; no necesita Python para importar.

## Dónde modificar

- `SourceRecords`: valida JSONL y rutas de referencias antes de escribir.
- `ApprovedMappings`: equivalencias explícitas de ticket/técnico.
- `ImportServiceLogs`: identidad, transacciones y conservación histórica.
- `ImportDocuments`: SHA256, tipo, rutas privadas y reintentos.
- `NinoxDetails` y `UI/Views/ninox.blade.php`: consulta original.
- `ServiceDocumentController`: apertura autorizada de adjuntos.
- `ImportarBitacoras.ps1` en la raíz del bundle: lectura local de PDF durante actualización.
