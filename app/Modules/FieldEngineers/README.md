# Ingenieros de servicio — 11.56.0

## Datos y acceso

Ocho ingenieros del archivo recibido, tabla de origen T. Ruta normal `/operations/field-engineers`, menú Ingenieros de servicio. La interfaz reutiliza el espacio de trabajo de Instituciones: tabla completa, selección visible, búsqueda, columnas configurables, agrupación, panel redimensionable, pestañas General/Contacto/Detalles/Relaciones y navegación anterior/siguiente conservando la pestaña. Se entrega en consulta mientras se cuadran los datos.

Los ingenieros de servicio son personas de negocio, separados de users. No se crean cuentas, correos ficticios, contraseñas, roles, invitaciones ni vínculos de identidad por coincidencia de nombre o correo. Dos ingenieros no traen email. El archivo no informa un estado laboral; no se presupone que todos estén activos. No se recibieron fotos. Se conservan nombres, teléfonos y ciudades originales, sin completar datos por deducción.

`field_engineers.view` permite el directorio y sus vínculos. `field_engineers.sensitive` controla la identificación, costo por hora y bono, que solo se envían al HTML para usuarios autorizados. El seeder agrega consulta a los roles operativos y consulta sensible solo a Admin/Administrador; SuperAdmin mantiene su permiso global. Las preferencias propias de vista siguen disponibles, pero las fichas recibidas no se crean, modifican ni borran desde el editor. La información sensible permanece en source_payload cifrado y oculto en serialización, sin propiedades públicas de Livewire.

## Relaciones verificadas

- 624 tickets contienen una referencia FSE que aparece en la lista de visitas/asignaciones de un único ingeniero. `ticket_field_engineer` conserva ticket, persona e identificador de asignación. No se convierte esa referencia en user_id ni se inventan fechas o estados de asignación. Un ticket no trae asignación.
- 363 servicios coinciden con el ingeniero verificado del ticket y el nombre histórico registrado en la bitácora. La cuenta de acceso del técnico permanece separada.
- Otros tres servicios sin ticket tienen nombre único y teléfono histórico coincidentes exactamente con el ingeniero recibido. Se conserva esa procedencia como registered_name_phone. No se inventa un ticket para poder vincularlos.
- Total: 366 servicios con ingeniero verificado, tres sin vínculo porque sus datos históricos de técnico están vacíos. No se atribuye el responsable actual del ticket a esos servicios. El origen de la vinculación queda en field_engineer_basis.
- Los ingenieros referencian 1213 asignaciones/visitas; 624 aparecen en los tickets recibidos y 589 no. Hace falta exportar la tabla de asignaciones FSE/Visitas para verificar fechas, cambios de responsable y el resto de referencias. No son las visitas mensuales del calendario y no se generan eventos a partir de esos números.
- Certificaciones, tareas y cronogramas se conservan como referencias originales hasta recibir sus tablas.

Los tickets y servicios continúan usando technician_id para la cuenta que opera el sistema y sus reglas actuales de visibilidad. La carga no cambia esa columna. FieldEngineer es una relación histórica independiente. Las tablas de tickets/servicios muestran el nombre histórico solo cuando se permite consultar Ingenieros de servicio; sus filtros y agrupación usan el mismo rótulo. Los registros ocultos al técnico siguen ocultos. La pestaña Relaciones del ingeniero consulta VisibleRecords antes de listar códigos y enlaces.

## Carga y repetición

`data:load-engineers` lee `database/imports/field-engineers/export/records.jsonl`. El instalador lo ejecuta después de cargar tickets, bitácoras y equipos. `--dry-run` valida sin escribir; `--source=` acepta otro JSONL compatible. Todas las filas se validan antes de iniciar la carga; la identidad es source_table/source_id, no el identificador local ni el nombre.

Cada persona conserva el origen completo cifrado, hash e identidad únicos. Repetir el mismo origen conserva datos y vínculos; un origen cambiado o código ocupado produce conflicto. Los vínculos existentes nunca se sustituyen. Las asignaciones ambiguas no se resuelven por coincidencia de número local. La vinculación usa bloqueos de fila y transacciones, y las restricciones de clave foránea conservan historial. La migración rechaza bajar si hay personas o vínculos recibidos.

La actualización mantiene .env/APP_KEY, usuarios y sus cuentas, equipos, marcas/modelos, relaciones previas de servicios/tickets y planificación mensual. No agrega paginación, no crea visitas de ejemplo y no hace respaldos automáticos. Los PDF continúan en su cola existente. Los seeders de datos reales quedan para después de cuadrar relaciones, como se acordó.

## Dónde modificar

- Configuration/Fields.php, Tabs.php, Layout.php, Columns.php y TableColumns.php: presentación y preferencias.
- Configuration/Permissions.php y database/seeders/FieldEngineerPermissionsSeeder.php: permisos.
- Import/EngineerSource.php e ImportEngineer.php: validación y conservación de personas.
- Import/LinkEngineerTickets.php: evidencia por referencia FSE única.
- Import/ServiceEngineerEvidence.php y LinkEngineerServices.php: corroboración histórica y procedencia.
- Queries/EngineerQuery.php: directorio, datos sensibles y relaciones visibles.
- UI/EngineerPage.php y UI/Views: interfaz normal, en consulta.
- DataReview/Queries/EngineerReview.php: conteos y siguientes cargas.

No existe un formulario de edición operativa de estas personas todavía; por ello no hay Rules de guardado ni acciones de creación en la ficha. Cualquier futura vinculación con Usuarios requiere una decisión y un flujo explícitos; no debe implementar coincidencias automáticas por email o nombre.
