# Validación v11.38.4

- PHP 8.3.6, dependencias exactas de composer.lock y WeasyPrint 57.2 (versión de Debian Bookworm).
- Suite completa: 105 pruebas y 378 comprobaciones aprobadas.
- Suite del módulo: 27 pruebas y 102 comprobaciones aprobadas, incluyendo generación real por la cola database/reports.
- Encabezado: compatibilidad con definiciones anteriores, persistencia en Livewire, estilos por línea, variables, lectura automática de logos y rechazo de estilos/rutas no autorizados.
- PDF real de 24 actividades: ocho páginas con el encabezado completo y 48 fotos; encabezado y ticket presentes en todas las páginas. Inspección visual de páginas 1 y 2; bordes conservados en páginas siguientes.
- Chromium: tres secciones del encabezado y control «Color de letra» accesibles; escritorio y móvil sin desbordamiento ni errores JavaScript.
- Apertura del PDF desde la sesión del navegador: HTTP 200, application/pdf y cabecera %PDF-.
- Acceso privado por propietario, caducidad, limpieza, conflicto entre sesiones y saneamiento de texto.
- Trabajador y scheduler configurados como www-data; entrypoint solo cambia propietarios cuando se ejecuta como root. La prueba de cambio de UID/propietarios no pudo ejecutarse aquí: el entorno restringe runuser y chown. Esta configuración debe verificarse con Docker Desktop en el equipo de destino.
- Código propio de esta entrega: máximo 300 líneas por archivo y 50 líneas por función PHP.

Este entorno no dispone de Docker ni PowerShell. No se afirma haber ejecutado la imagen Docker ni el instalador Windows. La configuración Compose se valida como YAML y los archivos del instalador se revisan y verifican mediante hashes. El paquete admite la base anterior, v11.38, v11.38.1 y v11.38.2, v11.38.3 y reaplicación de v11.38.4; no crea respaldos ni elimina datos existentes.

## Comprobaciones adicionales del editor

- Pruebas de selección: editar la línea impresa sin cambiar el nombre del bloque; fuente por bloque persistida; índices no autorizados rechazados; puente y recuadros ausentes del HTML para PDF.
- Navegador: clic sobre título, selección identificada, tamaño 19 pt aplicado, color cambiado en vivo, menú lateral abierto/cerrado, paneles ocultos/restaurados, sin errores JavaScript ni desbordamiento móvil.
- Vista previa en escritorio: ancho pasó de 926 a 1514 px al ocultar Bloques y Propiedades (viewport 1650 px).
- Mensaje de selección emitido desde la ventana principal ignorado; únicamente se admite el iframe del editor.
- PDF de prueba abierto mediante la sesión real de navegador: HTTP 200 y cabecera %PDF-.

## Separadores y tablas (v11.38.4)

- Separador independiente, fondo y letra configurables, títulos y datos automáticos/manuales en dos columnas con estilos sin bordes.
- Aviso retirado de HTML/PDF; conservación de datos al ocultar título del bloque, encabezados y etiquetas. Texto escapado y rechazo de variables/colores no autorizados.
- Persistencia de ambos bloques mediante Livewire sin reiniciar plantillas existentes.
- PDF real con diseño como la referencia: seis páginas de actividades; inspección visual del separador y columnas en la primera página.
- Tabla con dos columnas y 12 filas extensas por columna: tres páginas; los 24 finales de fila presentes y títulos repetidos en todas las páginas.
- Chromium: agregar separador y tabla, ajustar título/tamaño, guardar, ocultar títulos/etiquetas y conservar ocho valores. PDF abierto con HTTP 200; móvil sin desbordamiento ni errores JavaScript.

## Validación v11.38.5

111 pruebas y 398 comprobaciones aprobadas. Pruebas específicas: herencia y sobrescritura de estilos de tabla/columna/dato; negrita false; 5 y 7 datos en columnas distintas guardados mediante Livewire; tamaños 10.5 y 36 pt; fuentes permitidas; rechazo de RGB y CSS/fuentes arbitrarias; estilos de encabezado sin modificar cuerpo ni anchos.

Chromium: formato de encabezado (14 pt, Serif, centrado y HEX), formato de valores de columna (10.5 pt, Mono y HEX), guardar y recargar conservando colores/tamaños, PDF por cola abierto HTTP 200, móvil sin desbordamiento ni errores JavaScript. PDF real: 60 datos conservados en ocho páginas con títulos repetidos; tamaño extraído 10.5 pt y fuentes Serif/Mono incrustadas; revisión visual de la primera página.

Docker Desktop y PowerShell requieren comprobación en Windows: no se ejecutaron aquí.

## Validación v11.38.6

116 pruebas / 420 comprobaciones aprobadas. Alineación individual izquierda/centro/derecha; herencia por columna; sangría guardada y recuperada; adaptación de plantillas anteriores; rechazo de CSS y números fuera del rango; texto oculto y formato tipográfico conservado. PDF de 60 datos alineados en cinco páginas, sin pérdidas y con títulos repetidos; primera página revisada visualmente.

Docker Desktop y PowerShell no se ejecutaron en este entorno; deben comprobarse en Windows.

Chromium: elegir izquierda/centro/derecha en tres filas, sangría de 5 mm, guardar y recargar conservando valores. Se midió el inicio de la etiqueta respecto del margen de fila (sin columna fija). Las otras cinco filas conservaron su diseño anterior. PDF generado por cola y abierto HTTP 200; móvil sin desbordamiento ni errores JavaScript.

## Validación v11.38.7

124 pruebas / 461 comprobaciones aprobadas. Editor compacto: añadir firma al servicio y persistirla, modificar formato usando claves de Repeater, mover/quitar ítems, añadir/quitar columnas, cambiar variable a firma con defaults, rechazar índices inválidos y otros bloques. Render: firma independiente y firma de columna separadas, escape de nombre, imagen raster solo del contexto del informe; validación de origen, dimensiones y HEX.

PDF real: firma en servicio y firma independiente visibles en primera página; tabla de 60 datos conserva todos sus valores en cinco páginas. Primera página revisada visualmente. Docker/PowerShell requieren prueba en Windows.

Chromium: configuración avanzada cerrada inicialmente; selección de columna e ítem desde iframe, añadir firma al servicio, configurar 45 × 15 mm, nombre, color HEX y tamaño, guardar/recargar conservando valores, añadir firma independiente y obtener dos campos. PDF por cola abierto HTTP 200; editor compacto móvil sin desbordamiento ni errores JS. El panel se deshabilita brevemente durante cambios de selección para evitar editar el elemento anterior.
