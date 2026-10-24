# Informes técnicos

Interfaz normal: `/operations/technical-reports`. Mantiene Admin en `/admin`.

- General: selecciona un servicio visible y el título del informe.
- Plantilla: elige el formato guardado. Se utiliza su versión publicada, o el diseño en borrador si aún no tiene publicación.
- Revisión: consulta los datos capturados del servicio, ticket, cliente, institución, equipo y técnico, con actividades, cantidad de fotos y recomendaciones. Las secciones pueden colapsarse. «Actualizar datos · Nueva revisión» captura de nuevo los datos y el diseño.
- PDF: genera mediante la cola `database`, cola `reports`. Informa espera/proceso/error/vencimiento y abre el PDF de la revisión actual en otra pestaña para evaluar e imprimir. Requiere worker activo.

Desde Servicios → Informes se puede iniciar un borrador con `?new_for_service=ID`. No crea un registro hasta completar los campos obligatorios. El guardado es automático; guardar los mismos valores no crea otra revisión. La consulta y el polling tampoco crean revisiones. Las tareas pendientes conservan la copia de la revisión que recibieron; no se reemplaza su contenido cuando el informe cambia.

Configuración separada: `Configuration/Fields.php`, `Tabs.php`, `Layout.php`, `Columns.php`, `TableColumns.php` y `Rules.php`. La lista excluye las copias cifradas de datos e imágenes y conserva vistas personales, filtros, agrupación y navegación. Las acciones verifican la sesión del editor, permisos, alcance y versión de bloqueo; las vistas de lectura no permiten modificar.

El PDF es privado y temporal, con respuesta `private, no-store`. No se muestran enlaces listos de una revisión anterior como si fueran de la actual. Los datos ausentes se indican como «Sin registrar»; no se rellenan con datos ficticios. Los DEMO siguen identificados como ficticios.

El informe sigue siendo borrador. Faltan firmas, aprobación, emisión final y el historial permanente de documentos emitidos. Repuestos y componentes no se inventan mientras sus módulos no existan. No se añaden migraciones para esta interfaz.

Pruebas: `tests/Feature/TechnicalReportInterfaceTest.php` y `WorkflowReportsTest.php`.
