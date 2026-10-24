# Flujo operativo — v11.39.1

## Acceso y alcance

Los seis módulos usan Livewire y el diseño normal de usuarios, dentro de `/operations/`. Entra en http://localhost:8611/operations/clients con tu cuenta existente. No hay panel `/workspace`. `/admin` conserva la administración y las plantillas.

## Datos DEMO relacionados

La actualización crea de forma aditiva e idempotente 2 clientes, 2 instituciones, 3 equipos, 3 tickets, 3 servicios, 6 actividades y 3 informes borrador, más una plantilla DEMO independiente. Son casos ficticios con claves foráneas reales; no representan intervenciones realizadas.

- Red Clínica del Valle: monitor multiparámetro con preventivo completado y bomba de infusión con correctivo en ejecución.
- Centro Médico Horizonte: electrocardiógrafo con inspección en borrador.

Cada equipo está vinculado a cliente e institución; cada ticket a equipo y técnico; cada servicio a ticket; cada actividad e informe a servicio. Los informes capturan esos datos en una copia cifrada. No se crean cuentas ni contraseñas: se elige primero un Técnico/Engineer activo, o un administrador activo si no hay técnicos. La consola indica el nombre elegido. Los técnicos solo ven trabajo asignado; un administrador puede revisar los tres casos.

Los códigos DEMO permiten identificarlos y volver a ejecutar el instalador sin duplicar ni sobreescribir los registros existentes. Si aún no existe un técnico/administrador activo, la consola lo informa y no crea datos; después de disponer de uno puedes volver a ejecutar la actualización.

## Probar y editar

1. Abre Clientes y busca DEMO. Pulsa Editar para ver sus campos y la fila seleccionada.
2. En Equipos verás cliente e institución vinculados. En Tickets verás equipo y técnico.
3. En Servicios podrás revisar tipo, estado, resumen y recomendaciones. En Actividades, elige el servicio y registra hallazgos/resultados.
4. Nuevo registro abre una ficha lateral. Los selectores de relaciones muestran código y descripción; Guardar valida permisos y relaciones. Cerrar descarta cambios sin guardar.
5. Las actividades permiten añadir hasta cuatro fotos PNG/JPEG/WebP de 1 MiB cada una y abrirlas de forma privada.
6. En Informes técnicos abre un DEMO y guarda si deseas volver a capturar los datos actuales. Pulsa Generar PDF: siempre utiliza la cola database/reports. La lista comprueba el estado cada cinco segundos y muestra Abrir PDF al finalizar.
7. El PDF abre en otra pestaña y se puede imprimir desde el visor. Componentes y repuestos permanecen pendientes. Las firmas de plantilla no equivalen todavía a una firma capturada del servicio.

## Informes y cambios posteriores

Cada informe guarda una copia cifrada de los datos, del diseño, de los logos y de las evidencias. Cambiar el cliente, servicio o plantilla después no altera esa copia. Para actualizar un informe, abre Editar y guarda: se capturan otra vez los datos y aumenta su revisión. Cada generación PDF conserva su revisión original.

Si la plantilla tiene versión publicada, edita y publica una nueva versión para que se utilice al volver a guardar el informe. El borrador no sustituye una versión publicada automáticamente. El título del registro está disponible como variable Informe → Título; se imprime donde lo configures en la plantilla. También están disponibles dirección/correo/teléfono de cliente, código de equipo, código/fecha/resumen de servicio.

El PDF es privado y temporal (60 minutos por defecto), con el mecanismo de limpieza existente. El informe y sus datos permanecen en la base. Si expira el PDF, genera otro desde la misma copia del informe. Si el informe cambió de revisión, la lista indica Requiere generar y no ofrece el PDF viejo como versión actual.

La copia de un informe admite 200 actividades, hasta 8 MiB de fotos codificadas y 10 MiB totales incluyendo diseño y logos. Si supera esos límites, reduce imágenes o divide el trabajo entre servicios/informes; la aplicación muestra un error y no guarda una copia incompleta.

## Permisos

| Rol | Acceso inicial |
| --- | --- |
| SuperAdmin | Todo, por el control global existente |
| Administrador / Admin | Lectura y gestión de las seis entidades |
| Técnico / Engineer | Lectura de clientes/equipos; tickets, servicios, actividades e informes de trabajos asignados |
| CEO | Consulta de las seis entidades y generación/lectura de sus PDF, sin editar registros |
| Otros | Sin acceso operativo hasta asignar permisos |

Permisos: `workflow.access`, `workflow.view_all` y `{modulo}.view` / `{modulo}.manage`, con módulos clients, equipment, tickets, services, activities, technical_reports. Un rol personalizado debe disponer de lectura de sus entidades relacionadas. No hay aislamiento por empresa/institución en esta etapa: los técnicos pueden consultar el catálogo de clientes/equipos; el trabajo técnico sí se limita al técnico asignado.

## Conservación del historial

No hay eliminación de registros operativos en estas pantallas. Usa Activo/Estado cuando corresponda. Si un equipo ya tiene tickets no se cambia su cliente/institución; si un ticket tiene servicios no se cambia su equipo/técnico; si un servicio tiene actividades/informes no se cambia su ticket. Transferencias e historial de propietarios se implementarán como proceso específico.

No se puede eliminar una institución con equipos ni un usuario con historial técnico. Desactiva usuarios para revocar su acceso. La edición concurrente se protege con lock_version: si otra persona guardó antes, recarga y revisa los cambios.

## Límites actuales y siguientes pasos

La firma de la plantilla sigue disponible, pero no hay captura ni registro operativo de firmas. Tampoco hay aprobación/emisión final, auditoría completa o historial persistente de todas las revisiones anteriores del informe; los PDF temporales conservan su copia mientras estén vigentes. Los estados de ticket/servicio se administran manualmente y no ejecutan automatizaciones entre módulos.

Las fotos quitadas de una actividad no se borran físicamente de forma automática. Su limpieza y retención se definirán junto con las políticas de documentos/auditoría. Consulta PENDIENTES_ENTIDADES.md para el resto del alcance.

Docker Desktop y PowerShell deben verificarse en Windows: las pruebas de desarrollo usan PHP 8.3, SQLite, Chromium y el mismo motor PDF WeasyPrint, no una instalación Docker/MariaDB de producción.
