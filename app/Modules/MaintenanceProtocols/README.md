# Protocolos de mantenimiento · 11.61.0

## Uso

Servicio técnico → Protocolos muestra los registros recibidos con el mismo patrón de tabla, columnas configurables, agrupaciones y ficha con pestañas de Instituciones. General conserva nombre, código, versión y modelo informativo. Pasos e instrucciones contiene secciones desplegables con instrucciones, rangos, texto para informe y archivos privados. Relaciones muestra referencias de componentes pendientes, sin vincular equipos por nombre.

Servicios → seleccionar servicio → Protocolos → buscar y seleccionar → revisar pasos → Aplicar protocolo al servicio. Requiere servicio abierto, relaciones editables, vigencia explícita y permisos. Cada paso se convierte en una actividad sin resultado y sin fotos de evidencia. En Procedimientos aplicados se abren las actividades para registrar resultados y fotografías. La pestaña Protocolo de cada actividad conserva las instrucciones utilizadas.

No se completa el servicio al aplicar un protocolo. Su cierre sigue siendo la acción que cumple la visita contractual. Repetir la aplicación del mismo protocolo al mismo servicio no duplica las actividades. Se pueden aplicar otros protocolos al mismo servicio, por ejemplo el del sistema y el de un componente. El usuario debe verificar que correspondan al equipo; no existe una asignación automática por similitud de texto.

## Datos recibidos y pendientes

- 55 protocolos; 53 con obsoleto=false y dos con vigencia sin dato.
- 675 pasos: 652 vinculados, 23 pendientes. Quince esperan los protocolos de origen 113 y 114; ocho no tienen padre indicado.
- 189 adjuntos: 188 imágenes y un PDF. Se verifica cada SHA-256 antes de importar.
- El paso de origen 1230, del protocolo 76, no tiene instrucciones ni texto para informe. Se conserva, pero impide aplicar ese protocolo hasta recibir su contenido.
- Los códigos y nombres aparentemente intercambiados o atípicos se conservan tal como llegaron. No se corrigen por intuición.
- Componentes y modelos no se vinculan por IDs locales ni por coincidencia textual. Falta recibir componentes y referencias verificables al catálogo.

La consulta del origen es de solo lectura. No se inventan tareas, clientes, equipos, resultados ni fotografías. Los originales completos se conservan cifrados con APP_KEY. Las instrucciones de más de 5000 caracteres permanecen íntegras en la instantánea; la descripción editable de la actividad usa el texto de informe o una indicación para consultar las instrucciones completas.

## Informes y archivos

Se respeta el indicador recibido de inclusión en informe. Los pasos internos permanecen como actividades consultables, pero se excluyen de nuevas capturas de informe. Las capturas emitidas anteriormente no se modifican. Los adjuntos del protocolo son referencias, nunca evidencias del trabajo. Los informes mantienen su generación mediante cola.

Archivos en storage/app/private/maintenance-protocols (disco local configurado), servidos por ruta autenticada y permiso maintenance_protocols.view, sin enlace público de storage. Se entregan por streaming desde archivo, sin incrustar imágenes en las listas. APP_KEY y almacenamiento deben conservarse.

## Carga repetible

El instalador ejecuta `data:load-protocols` automáticamente después de migraciones, permisos e importaciones anteriores. También permite `--dry-run` y `--source=RUTA` con protocols.jsonl, steps.jsonl, images.json e images/. Valida identidad, relaciones bidireccionales, formato de adjuntos e integridad. Si cambia el contenido de un registro de origen ya importado, se detiene para revisión en lugar de sobrescribirlo. Repetir el mismo origen conserva identidades y aplicaciones existentes.

El bloqueo de carga evita importaciones simultáneas. La creación de aplicaciones bloquea el servicio y el protocolo en transacción, con índice único servicio/protocolo. No se pueden mover actividades de protocolo a otro servicio. Se respetan los permisos y la visibilidad del técnico en servidor.

## Estructura

- Configuration: Fields, Tabs, Columns, TableColumns, Layout, Permissions.
- Models: MaintenanceProtocol, ProtocolStep, ProtocolFile, ProtocolApplication.
- Import: ProtocolSource valida; ImportProtocols carga y conserva el origen.
- Actions/ApplyProtocol: aplica una sola vez, valida y crea actividades con instantáneas.
- Queries: ProtocolQuery, ActivityProtocolContext, ProtocolReview.
- UI: ProtocolPage, ServiceProtocols y vistas modulares.
- UI/Controllers/ProtocolFileController: acceso privado a archivos.
- routes/modules/maintenance-protocols.php: rutas operativas.
- database/seeders/ProtocolPermissionsSeeder.php: permisos aditivos por rol.
- tests/Feature/MaintenanceProtocolTest.php: importación real, aplicación, informes, permisos y seguridad.

No se introduce paginación. Las listas relacionadas de actividades e informes del servicio también muestran todos sus registros visibles.
