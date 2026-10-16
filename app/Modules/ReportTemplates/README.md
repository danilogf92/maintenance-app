# Plantillas de informes (v11.38.4)

Entrada: `/admin/report-templates`. Editor integrado en Filament 4 y Livewire 3.

## Organización

- **Configuration/Blocks.php**: bloques disponibles, variables iniciales y opciones de tablas.
- **Configuration/Variables.php**: catálogo permitido de variables; nunca ejecuta PHP, Blade ni SQL escrito por el usuario.
- **Configuration/Fields.php**: formularios de propiedades de Filament.
- **Configuration/Layout.php**: papel y estilos predeterminados para plantillas nuevas.
- **Configuration/Rules.php**: límites y validación, también aplicados antes de renderizar.
- **Configuration/Permissions.php**: acceso al panel y permiso `report-templates.manage`.
- **UI/ReportTemplateResource.php**: listado, creación y duplicación.
- **UI/Pages/EditReportTemplate.php**: coordina el editor, guardado, publicación y pruebas.
- **UI/Concerns/EditsBlocks.php**: seleccionar, agregar, quitar y mover bloques.
- **UI/Views/**: editor de tres columnas y controles.
- **Rendering/TemplateRenderer.php**: HTML común para vista rápida y PDF.
- **Rendering/Views/Blocks/**: presentación impresa de cada sección.
- **Rendering/SafeRichText.php**: formato enriquecido sin atributos activos, scripts ni recursos externos.
- **Queries/TemplateSampleData.php**: datos de ejemplo y selección de institución real.
- **Models/**: borrador y versiones publicadas inmutables.

## Añadir una variable

1. Agregar una clave y su etiqueta en `Configuration/Variables.php`.
2. Proporcionar su valor con la misma ruta en el adaptador de datos. Ejemplo: `equipment.serial` se obtiene de `['equipment'=>['serial'=>'...']]`.
3. Agregarla a los campos iniciales del bloque, si se desea. Los cambios de predeterminados no modifican plantillas existentes.
4. Probarla en una plantilla nueva y generar un PDF de prueba.

## Añadir un bloque

1. Registrar el tipo y etiqueta en `Blocks.php`.
2. Definir propiedades en `Fields.php` y validar nuevos valores en `Rules.php`.
3. Crear una vista pequeña en `Rendering/Views/Blocks/`.
4. Agregar la selección de esa vista al documento principal. No usar un nombre de vista recibido sin validar.

## Datos reales y alcance del ZIP

Este proyecto contiene usuarios e instituciones. Equipos, clientes, tickets y bitácoras aparecen como «Próximamente»; no existen sus modelos ni registros de servicio.

Se puede elegir una institución real; equipo, ticket y actividades siguen usando datos de ejemplo. El aviso visual se retiró en v11.38.4 por petición del usuario; el adaptador y el indicador is_sample de los datos se mantienen.

Al implementar Tickets, reemplazar `TemplateSampleData` en una acción de generación definitiva por una consulta autorizada al ticket y sus relaciones. Mantener el contrato de datos documentado aquí y volver a comprobar el permiso de acceso al ticket. Retirar el aviso visual no sustituye la consulta de registros reales.

## Diseño e impresión

El editor reorganiza bloques, variables y columnas; no utiliza posiciones absolutas. La vista rápida muestra contenido HTML continuo y hasta cuatro actividades para no recargar el editor. Los saltos y el número exacto de páginas se verifican con el PDF real.

Las tablas repiten el encabezado y continúan automáticamente. Las fotos se agrupan en filas pequeñas; una actividad con muchas fotos se divide en varias filas con la descripción repetida y «continuación». Se evita cortar una foto y su leyenda. Textos mayores que una página pueden fluir según el motor de impresión.

Firmas: esta versión muestra líneas para firma manual; no implementa firmas digitales ni imágenes de firmas de registros todavía inexistentes.

Pruebas: `tests/Feature/ReportTemplatesTest.php`, `ReportQueueTest.php` y `ReportPdfIntegrationTest.php`.

## Extender el encabezado

- `Configuration/Header.php`: valores iniciales y adaptación de definiciones antiguas. Los valores antiguos se conservan; no se modifican versiones publicadas en base de datos.
- `Configuration/HeaderFields.php`: tres paneles de Filament y líneas manuales/dinámicas.
- `Configuration/HeaderRules.php`: validación de estructura, estilos, variables y límites.
- `Rendering/HeaderRenderer.php`: ancho proporcional y texto resuelto; no evalúa código recibido del usuario.
- `Rendering/Views/Blocks/header.blade.php`: tabla de tres columnas con datos escapados.
- `Rendering/TemplateRenderer.php`: captura el logo global y los logos por origen en la copia inmutable de la generación.

Contrato del logo de fabricante: proporcionar `equipment.factory_logo` como ruta relativa del disco público (`report-assets/...png` o `branding/...png`), nunca una URL ni una ruta absoluta. Proporcionar `equipment.brand` como nombre alternativo. No aceptar esa ruta directamente desde parámetros de consulta del usuario; obtenerla de la relación autorizada del equipo. `TemplateSampleData` permanece explícitamente de prueba y no inventa un modelo de fabricante.

Los campos `header_columns.left/center/right` guardan ancho, origen de logo, altura, alineación, borde y líneas. Cada línea guarda modo, texto, variable, color hexadecimal, tamaño y negrita. Las versiones antiguas se adaptan en memoria antes de validar/renderizar; al guardar el borrador se persiste el nuevo formato. Publicar crea una nueva versión sin alterar las anteriores.

La cola pasa definición, datos y logos ya capturados a Python. Cambiar un logo o una plantilla después de solicitar el PDF no altera esa solicitud.

## Selección de elementos y paneles plegables

- `UI/Concerns/SelectsPreviewElement.php`: valida bloque, columna y posición de línea. La selección está protegida con Locked; los índices recibidos se verifican antes de cambiar el estado.
- `Rendering/PreviewRenderer.php`: añade el puente y los estilos de selección solamente a la vista rápida. La cola sigue usando TemplateRenderer para el documento limpio.
- `UI/Views/Partials/preview-bridge.blade.php`: maneja clic/Enter/Espacio y envía únicamente índices. El iframe permite scripts controlados pero conserva origen aislado (sin allow-same-origin).
- `UI/Views/editor.blade.php`: acepta mensajes cuyo source es exactamente la ventana del iframe; comprueba tipos antes de llamar a Livewire. Mensajes del documento principal u otras ventanas se ignoran.
- `UI/Views/Partials/selected-element.blade.php`: controles rápidos ligados al mismo estado del formulario. Las posiciones numéricas se resuelven a claves de Repeater para no editar otra fila.
- `public/css/report-template-editor.css`: distribución según paneles abiertos y colores claro/oscuro.
- `AdminPanelProvider`: controles nativos de Filament para colapsar el menú.

La selección no se guarda en la plantilla. `font_size` es un override opcional por bloque; las plantillas anteriores siguen heredando la fuente del papel. Los atributos data-preview de las vistas son identificadores inertes; scripts y recuadros se añaden exclusivamente mediante PreviewRenderer.

## Separadores y tablas de datos

- `Configuration/DataTable.php`: columnas y filas iniciales, adaptación de configuración incompleta.
- `Configuration/DataTableFields.php`: formularios plegables para estilos, columnas y filas.
- `Configuration/DataTableRules.php`: colores, variables permitidas, límites de filas y opciones de títulos.
- `Rendering/Views/Blocks/separator.blade.php`: franja independiente con texto escapado.
- `Rendering/Views/Blocks/data-table.blade.php`: una a tres columnas; filas alineadas, texto manual/dinámico y encabezados repetibles. Cada fila se renderiza separadamente para permitir el salto de página.
- `Rendering/Views/Blocks/title.blade.php`: título opcional compartido por las tablas y demás bloques.

`show_title` y `show_table_header` son independientes y opcionales en definiciones antiguas; por defecto se conserva su presentación anterior. Datos en columnas tiene además `data_sections`, `show_labels`, `bordered` y colores de títulos/valores/etiquetas. Cada sección define su título y filas `{label, mode, variable, text}`. No se aceptan consultas o código como variables.

El bloque separador usa `title`, `font_size`, `separator_background`, `separator_color` y `separator_alignment`. No genera tablas ni consulta registros. Las plantillas guardadas no se modifican por el seeder; el usuario agrega y posiciona estos bloques desde el editor.

La prueba de diseño retirada solamente afecta la presentación; QueueReportPdf continúa tomando una copia de datos de prueba hasta disponer del adaptador de tickets reales.

## Formato tipográfico v11.38.5

`Configuration/TextStyle.php` centraliza fuentes permitidas, tamaños sugeridos, campos, validación y CSS. `table_header_style` configura los encabezados de tablas. Cada entrada de `data_sections` admite `title_style`, `label_style` y `value_style`. Cada fila de `fields` admite `label_style` y `value_style`. Las propiedades son `size` (6–72 pt, numérico), `font` (`sans`, `serif`, `mono`), `color`/`background` (HEX), `alignment` (`left`, `center`, `right`), `bold` e `italic`. Los valores nulos/vacíos heredan; false/0 se conservan.

`TextStyle::css()` recibe capas en orden de prioridad. Su entrada siempre pasa primero por `Rules`, que rechaza propiedades, CSS y fuentes ajenas a la lista permitida. No recibe CSS libre del usuario. Los estilos heredados de v11.38.4 (`section_background`, `section_color`, `label_color`, `value_color`) se mantienen como base para no cambiar plantillas guardadas.

`App/Support/Forms/HexColor.php` (ruta real `app/Support/Forms/HexColor.php`) es el único creador de selectores de color propios. Fuerza HEX y valida #RRGGBB. Se reutiliza en Configuración → Colores y en las plantillas. Los controles rápidos Blade aceptan el mismo código con validación del servidor.

Límite de datos: 3 columnas y 30 filas por columna. Para más contenido usa otro bloque; los bloques y filas continúan por páginas. No se reescribe automáticamente ninguna definición guardada al instalar.

## Alineación de ítems v11.38.6

`data_sections.*.item_alignment` guarda `left`, `center`, `right` o `legacy`. `fields.*.item_alignment` guarda esos valores o `inherit`; `fields.*.item_indent` guarda 0–30 mm. `Configuration/DataTable::upgrade()` añade `legacy` a columnas guardadas sin esta propiedad y `inherit`/0 a sus filas. Se adapta al cargar/validar, sin migrar ni reescribir registros al instalar. Las columnas nuevas usan `left`.

`Rendering/Views/Blocks/data-table.blade.php` resuelve la alineación de fila/columna y renderiza etiqueta y valor como spans en un contenedor con `text-align` y sangría. La sangría se suma a 1 mm de margen interior. El diseño `legacy` conserva la tabla interior y sus estilos originales. Las propiedades se validan en `DataTableRules`: no se recibe CSS libre.

`ReportItemAlignmentTest` cubre cambios por fila, herencia, persistencia y reapertura, conservación de tablas previas, ocultar etiquetas conservando valores/estilos y rechazo de alineación o sangría inválidas.

## Editor compacto y firma v11.38.7

`UI/Concerns/SelectsDataItems.php` mantiene índices ordinales Locked y los convierte a claves reales de Repeater. `EditsDataItems.php` añade/mueve/quita ítems y columnas sin depender de UUID en el navegador. `UI/Views/Partials/data-editor.blade.php` y `DataEditor/` muestran lista + un panel de edición/formato. El formulario Filament completo sigue disponible, plegado, y valida al guardar. No se eliminan opciones ni se reescriben valores existentes.

La vista previa emite `report-data-select` desde el iframe aislado. El padre comprueba `event.source`, tipo e índices enteros. `selectDataItem()` valida tipo de bloque, columna y fila. Los identificadores data-preview son inertes en el PDF; solo PreviewRenderer agrega scripts/recuadros. Celdas vacías seleccionan la columna, no una fila inexistente.

`Configuration/Signature.php` centraliza defaults, campos y reglas. `Blocks::make('signature')` crea un bloque individual; `DataTable::signatureField()` crea una fila `mode=signature`. La configuración `signature` contiene source (blank/technician/recipient), alignment (bloque), width/height, show_line, line_color, caption, show_name, name_variable/name y style. Al cambiar un ítem existente a signature se completan sus opciones requeridas.

`Rendering/SignatureRenderer.php` solo acepta imágenes PNG/JPEG/WebP en data URI, hasta 2 MiB de bytes. Rechaza SVG, URL externas y rutas de archivos. El contexto del informe puede aportar `signatures.technician.image` o `signatures.recipient.image`, ya autorizados y capturados para ese informe. No se consulta una firma maestra de usuario y no existe un campo de subida de firma en la plantilla. La integración de captura y datos reales de firmas queda fuera de esta actualización; el proveedor de prueba actual muestra espacio para firmar.

`Rendering/Views/Blocks/signature-field.blade.php` se reutiliza en bloque y columna. Respeta dimensiones, mantiene firma y nombre juntos y permite continuar las filas en otra página. Fuentes, tamaños, colores y contenido se validan antes de generar HTML/PDF.
