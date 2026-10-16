## v11.38.7 — Editor compacto y firma individual

- Columnas e ítems en una lista compacta. Un único panel de contenido y formato para el elemento seleccionado. La configuración completa queda plegada y conserva todas las opciones anteriores.
- Selección de columna/ítem desde la vista previa, con recuadro y validación de índices. Añadir/quitar columnas y añadir, quitar o mover ítems sin abrir repetidores anidados.
- Firma como bloque independiente y como ítem en cualquier columna (por ejemplo Información del servicio). Configura tamaño, posición, línea HEX, nombre, texto inferior y formato.
- Espacio para firma en papel; renderiza una imagen raster de la firma del informe cuando el contexto la proporciona. No guarda firmas reutilizables en plantillas ni implementa captura manuscrita en esta actualización.
- Conserva datos, versiones, colas y paginación. 124 pruebas / 461 comprobaciones.

## v11.38.6 — Alineación real de cada ítem

- Control guardado `item_alignment` por fila y valor predeterminado por columna: izquierda, centro, derecha o diseño anterior. Cada ítem puede heredar o sobrescribir la alineación de su columna.
- Etiqueta y valor se alinean como un conjunto sin celda de etiqueta de ancho fijo. Sangría por ítem de 0–30 mm, admite decimales.
- Conserva el diseño de las tablas anteriores hasta elegir una alineación. Los bloques nuevos empiezan a la izquierda.
- Valida valores y mantiene formato, variables, colores, cola y paginación.
- 116 pruebas / 420 comprobaciones aprobadas.

## v11.38.5 — Formato de columnas y datos

- Encabezados de tablas: fondo y letra HEX, fuente, puntos, alineación, negrita y cursiva. Formato propio por columna de datos.
- Cada columna admite de 1 a 30 datos con etiqueta + variable del sistema o texto manual. Formato independiente de etiquetas y valores.
- Herencia tabla → columna → dato. Los valores vacíos heredan; desactivar negrita prevalece sobre el formato superior.
- Tamaños en puntos reales (como Word), de 6 a 72, incluidos decimales. Fuentes disponibles: DejaVu Sans, Serif y Sans Mono.
- Todos los controles de color de aplicación y plantillas usan HEX #RRGGBB explícito y validación del servidor.
- Conserva plantillas, datos, cola y continuación automática. 111 pruebas / 398 comprobaciones; navegador y PDF real verificados.

## v11.38.4 — Separadores y datos en columnas

- Retirado el aviso «PRUEBA · DATOS DE EJEMPLO · NO EMITIDO» del informe y de la vista previa.
- Nuevo bloque «Título / separador»: franja con texto, fondo, color de letra, alineación y tamaño en puntos.
- Nuevo bloque «Datos en columnas»: una a tres columnas con títulos independientes, etiquetas y datos automáticos o texto manual; diseño sin bordes como la referencia.
- Controles independientes para mostrar/ocultar título del bloque, títulos de columnas y etiquetas de campos; repetición de encabezados al continuar la tabla.
- Se conservan anchos de fotografía y numeración aunque se oculten los encabezados.
- La vista desplaza la selección a una posición visible después de actualizarse.
- Plantillas existentes conservadas sin alterar su diseño automáticamente.
- 105 pruebas y 378 comprobaciones aprobadas; tabla de 24 datos extensos continuada en tres páginas sin perder valores.

## v11.38.3 — Editor con selección visual y paneles plegables

- Menú lateral de Filament colapsable; se inicia compacto al abrir el editor y se puede desplegar.
- Botones para ocultar y volver a mostrar Bloques, Propiedades y Vista previa; la vista utiliza el espacio disponible.
- Grupos de propiedades plegados: información/distribución, repetición, columnas del encabezado, logos, variables, fotografías y contenido de texto.
- Selección directa de bloques, columnas y líneas del encabezado en la vista previa, con recuadro azul e indicador «Editando».
- Controles rápidos para texto, tamaño, color, negrita y variable de la línea seleccionada; fuente opcional por bloque.
- Puente de selección aislado dentro de iframe sandbox; acepta mensajes solamente del iframe del editor y valida la selección en servidor.
- Los recuadros y scripts del editor no forman parte del PDF.
- 100 pruebas / 357 comprobaciones aprobadas; comprobación en navegador de escritorio y móvil.

## v11.38.2 — Encabezado configurable y permisos de PDF

- Tres columnas de encabezado con ancho, alineación, logo de origen y bordes propios.
- Líneas manuales o variables con color, tamaño y negrita; se guardan en la plantilla.
- Logo de empresa desde configuración; logo de fabricante desde datos del equipo o guardado una vez en la plantilla.
- Encabezado completo opcional en todas las páginas; textos escapados y estilos validados.
- Adaptación compatible de plantillas anteriores, manteniendo textos, variables y logos personalizados.
- Cola y limpieza ejecutadas como www-data; reparación de propietarios al iniciar la aplicación.
- 96 pruebas y 341 comprobaciones aprobadas. PDF real con encabezado en ocho páginas.

## v11.38.1 - Corrección de construcción Docker

- Paquete Debian correcto: weasyprint.
- Prueba de importación y renderizado PDF durante la construcción.
- Instalador compatible con actualización v11.38 parcialmente aplicada.

## v11.38 - Plantillas PDF con colas

- Editor de tres paneles integrado en Filament, bloques, variables, logos y estilos.
- Creación, duplicación, guardado y publicación de versiones de plantillas.
- PDF de prueba con datos de ejemplo e institución seleccionable; siempre en cola.
- Paginación automática de tablas/fotos y repetición de encabezados.
- Copia cifrada de datos/diseño, archivos temporales privados y limpieza automática.
- Trabajador y programador Docker, documentación y pruebas.

# Cambios

## v0.1.0 — 2026-10-09

- Plantilla independiente extraída del patrón de usuarios de projectsIA.
- Institución obligatoria, varias asignaciones de rol, cargo y datos personales.
- Ingreso normal y panel Filament con autorización en servidor.
- SuperAdmin inicial, protección de privilegios y marca global.
- Docker local, instalador Windows con destino nuevo, manual y fases.
- Cursos y demás alcance de capacitación pendientes según el plan.
- Actualizaciones de seguridad en dependencias sin cambiar las versiones principales.
