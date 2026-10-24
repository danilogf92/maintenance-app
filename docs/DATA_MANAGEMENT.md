# Gestión de datos: clientes y centros médicos

## Uso

- Origen: `data/configuracion.ini`, una línea `dir="RUTA_COMPLETA"`.
- Correspondencias: único archivo `data/correspondencias.json`, nombres españoles de carpetas → tablas inglesas.
- Preparación: `data/PrepararClientes.cmd`. El actualizador v11.63.0 también intenta realizarla.
- Carga: Admin → Gestión de datos → Carga de datos → Cargar clientes y centros.
- Consulta: `/operations/clients` y `/operations/institutions`.
- Resultado: estados y cantidades por entidad, observaciones por ID de origen, descarga de ZIP.

Se reciben ambas tablas juntas, con identidades A y B verificadas. Si existen varias copias de una misma tabla, se exige resolverlas; no se elige una arbitrariamente. Se prefiere export/records.jsonl sobre las copias individuales.

## Campos

| Fuente | Destino | Criterio |
| --- | --- | --- |
| A.client_name_or_business_name | clients.name | Obligatorio; sin nombre válido se rechaza ese registro |
| A.ruc_or_identification | clients.tax_id | Conserva ceros cuando el origen es texto |
| A.billing_email | clients.email | Se valida como un correo; valores ambiguos quedan pendientes |
| A.phone | clients.phone | Texto o número entero, sin inventar prefijos |
| A.address | clients.address | Texto hasta el límite configurado |
| B.name | institutions.name | Obligatorio |
| B.address | institutions.address | Campo visible en General |
| B.city_id, B.contact_id | Pendiente | Se conserva referencia; no se usa como texto de ciudad/contacto |
| A.location_id, A.asociated_contacts | Pendiente | No se presupone correspondencia con IDs locales |

Se generan códigos internos deterministas desde tabla e ID de origen. El tipo de institución no se deduce del nombre. Todos los campos originales se conservan en `data_source_records.payload`, cifrado con APP_KEY. No se crean cuentas de usuario.

## Arquitectura

- `data/PrepararClientes.ps1`: lee la ruta de Windows, encuentra DataSanitized, normaliza los dos ficheros y publica el manifiesto al terminar. Los JSONL originales conservan su contenido.
- `Support/CatalogPackage.php`: manifiesto, tamaño, SHA-256, conteos, identidades y duplicados. La tabla destino solo puede ser clients o institutions.
- `Actions/StartCatalogImport.php`: permiso data_management.manage, correspondencias guardadas, copia de trabajo independiente y envío a cola. Impide iniciar otra carga mientras haya una pendiente.
- `Jobs/ImportCatalogPackage.php`: conexión database, cola data-imports; vuelve a validar el paquete y los permisos del solicitante; escritura transaccional.
- `Import/CatalogRecord.php`: valida cada campo, crea solo identidades nuevas; los datos cambiados del origen son conflictos y no sobrescriben ediciones.
- `Models/DataSourceRecord.php`: identidad de origen → registro local, hash y original cifrado.
- `Actions/CatalogImportDiagnostics.php`: ACK, estado, errores y vínculos de equipos pendientes.
- `UI/Views/imports.blade.php`: cantidades preparadas, estados, historial de ejecuciones y descarga.

## Archivos de trabajo y diagnósticos

El puente guarda su copia de trabajo bajo `storage/app/private/data-management/inbox/<paquete>/`. No es un respaldo ni modifica el origen. No copia PDF ni fotografías.

Al encolar se fija otra copia privada en `storage/app/private/data-management/imports/<ejecucion>/`. Cambiar la carpeta original después no altera esa ejecución.

Cada carga terminada guarda automáticamente `status.json`, `errors.json` y `ack.json` en la carpeta privada de la ejecución. El ZIP descargable añade `pending-links.json` con las referencias de los equipos. Las observaciones no incluyen RUC, correo ni nombres originales de los registros.

La preparación genera también `data/resultado/preparacion-<paquete>/errors.json` y `ack.json`. Su ACK dice preparado/imported=false: preparar no equivale a importar.

## Cola y recuperación

El servicio `data-worker` se incluye en Compose y lo arranca Actualizar.cmd. Consume database/data-imports con timeout de 210 segundos y retry_after mínimo de 300 segundos. Los informes usan otra cola y otro proceso.

Si una carga falla, descarga el diagnóstico, corrige el origen o la correspondencia, vuelve a preparar y pulsa cargar. No cambies manualmente su estado mientras esté en ejecución. El worker rechazará una carga si el solicitante perdió acceso administrativo.

## Pendiente

- Historial de clientes y centros de cada equipo; no confundir referencias a historiales con IDs de clientes o instituciones.
- Entidades de ubicación y contactos referenciadas.
- Contratos y coberturas, actividades y demás tablas del inventario.
- Update destructivo, Erase y generación/ejecución de Seed generales. La carga inicial actual agrega identidades nuevas y reporta conflictos.

## Historiales disponibles desde v11.64.0

La carga QC/RC y resolución de equipos se documenta en [EQUIPMENT_ASSIGNMENT_HISTORIES.md](EQUIPMENT_ASSIGNMENT_HISTORIES.md). En la lista anterior, la carga de esas dos tablas ya está implementada; la revisión de los casos ambiguos sigue pendiente.
