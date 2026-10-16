# Plantillas PDF: uso y operación

## Actualizar en Windows

Extraer `maintenance-app-actualizacion-v11.38.4.zip` en una carpeta fuera del proyecto. Abrir CMD en la carpeta extraída:

```cmd
Actualizar.cmd "C:\Users\danil\OneDrive\Documentos\DevelopmentDanilo\maintenance-app"
```

Usar la ruta real de tu proyecto. Docker Desktop debe estar iniciado.

El comando verifica los archivos, detiene servicios, copia el cambio incremental, construye el generador PDF, aplica migraciones aditivas y arranca la aplicación, el trabajador de cola y la limpieza automática. Reinicia cachés de Laravel, Filament (mediante los scripts de Composer), permisos y páginas. No crea respaldos, no modifica `.env`, no borra usuarios ni instituciones y no reinicia los volúmenes de MariaDB.

Si detecta que editaste un archivo que necesita reemplazar, se detiene antes de copiar: compara tu modificación con el ZIP original. Si falla una etapa de Docker, corrige el error y repite el mismo comando; admite reaplicación. No continúes a otra actualización hasta completar esta.

La primera construcción incluye WeasyPrint y puede tardar. No requiere nuevos puertos, Redis ni servicios de pago.

## Crear y probar

1. Entrar con SuperAdmin o administrador autorizado.
2. Abrir **Plantillas de informes** en Filament: `http://localhost:8611/admin/report-templates`.
3. Editar la plantilla inicial o crear otra. También puedes duplicarla.
4. Seleccionar un bloque a la izquierda y configurar las propiedades a la derecha.
5. Mover bloques con las flechas, agregar texto o secciones y ocultar bloques vacíos.
6. Abrir **Papel, diseño y datos de prueba** para cambiar papel, orientación, márgenes, fuente, color y pie.
7. Elegir 4, 24 u 80 actividades. Elegir una institución real si deseas probar sus datos.
8. Pulsar **PDF de prueba**. Siempre se envía a la cola, incluso una prueba pequeña.
9. Esperar **En cola → Generando PDF → PDF listo**. Pulsar **Abrir PDF en otra pestaña** para imprimir o descargar.
10. Guardar borrador. **Publicar versión** guarda y crea una versión inmutable para futuras integraciones de informes.

La prueba admite cambios sin publicar. Si continúas editando durante la generación, el PDF utiliza la copia capturada al pulsar el botón, incluidos logos y datos. Cambiar de plantilla o recargar puede descartar cambios sin guardar.

La vista rápida es HTML continuo y muestra hasta cuatro actividades. El PDF es la referencia para paginación. La interfaz admite tema claro/oscuro y reorganiza los paneles en pantallas pequeñas. El texto libre usa formato básico de Filament; no permite cargar HTML ejecutable.

## Colas y archivos temporales

Se utiliza la conexión **database** explícitamente y la cola **reports**. No usa el controlador `sync` aunque `.env` lo configure para otros trabajos.

- `reports-worker`: genera los PDF. Dos intentos, 210 segundos de tiempo máximo por trabajo.
- `reports-scheduler`: ejecuta limpieza cada cinco minutos.
- `retry_after`: al menos 300 segundos, superior al tiempo del trabajador para evitar dos ejecuciones simultáneas por visibilidad.
- Resultado privado: `storage/app/private/report-previews/<uuid>/report.pdf`.
- El HTML intermedio se elimina al finalizar o fallar el renderizado.
- Caducidad: 60 minutos, configurable con `REPORT_PDF_TTL_MINUTES` en `.env`.
- Al caducar se bloquea el acceso; limpieza automática elimina PDF y copia cifrada de sus datos en la siguiente ejecución, normalmente en hasta cinco minutos.
- Los trabajos que siguen procesándose tienen un margen de diez minutos antes de limpieza de emergencia.
- El documento solamente puede abrirlo el usuario que lo solicitó, mientras mantenga permisos. No hay enlace público.
- Máximo tres solicitudes pendientes por persona y diez solicitudes por minuto.

No se conservan PDF definitivos ni se crea una biblioteca permanente de pruebas. Las descargas que guardes manualmente en tu computador permanecen allí.

## Comprobar los procesos

```cmd
docker compose ps
docker compose logs --tail=80 reports-worker
docker compose logs --tail=80 reports-scheduler
docker compose exec app php artisan reports:clean-previews
```

Si la prueba queda en cola, comprobar que `reports-worker` está activo. Si falla, revisar sus logs y `storage/logs/laravel.log`; la interfaz no expone detalles internos. El botón Reintentar crea una prueba nueva con la configuración actual.

Después de modificar configuración:

```cmd
docker compose exec app php artisan optimize:clear
docker compose restart reports-worker reports-scheduler
```

Pruebas automáticas del módulo:

```cmd
docker compose exec app php artisan test --filter=Report
```

## Servidor sin Docker

Requiere PHP 8.3+, las extensiones Laravel existentes, PCNTL para control de tiempo del trabajador, Python 3 con WeasyPrint y fuentes DejaVu. En Debian Bookworm: `weasyprint` y `fonts-dejavu-core`.

Configurar `REPORT_PDF_PYTHON` si Python está en otra ruta. Ejecutar permanentemente, con Supervisor o systemd:

```text
php artisan queue:work database --queue=reports --sleep=2 --tries=2 --timeout=210 --memory=384
```

Configurar cron cada minuto para `php artisan schedule:run`; no duplicar cron y `schedule:work` en la misma instalación. No publicar `storage/app/private`.

## Qué está preparado y qué depende de otros módulos

Ya se puede diseñar, guardar, duplicar, publicar y probar PDF paginados. Las instituciones usan datos existentes. Equipos/tickets/clientes aún no están implementados en el ZIP, por lo que los informes de servicio reales se conectarán a esos modelos cuando estén disponibles. No se puede elegir un ticket real inexistente.

Para extender variables, bloques o presentación, comenzar en `app/Modules/ReportTemplates/README.md`. La generación en segundo plano se encuentra en `app/Modules/Reports/`. Cada archivo propio nuevo se mantiene bajo 300 líneas y cada función bajo 50 líneas.

## Encabezado de tres columnas (v11.38.2)

1. Abre `/admin/report-templates`, edita una plantilla y selecciona su bloque de encabezado.
2. En «Propiedades del bloque», despliega «Encabezado · Izquierda», «Centro» o «Derecha».
3. Elige ancho relativo, alineación y origen del logo. Los anchos se ajustan proporcionalmente: 28/47/25 ocupan todo el ancho.
4. En «Líneas de texto», abre la flecha de una línea o pulsa «Añadir texto o variable».
5. Selecciona «Texto manual» o «Texto + dato del registro». En el segundo caso escribe el texto previo (por ejemplo `Reporte N.º: `) y selecciona «Informe → Número».
6. Usa «Color de letra», «Tamaño de letra (pt)» y «Negrita» en cada línea. Puedes arrastrar las líneas para reordenarlas.
7. Activa el borde y escoge su color por columna si lo deseas.
8. Guarda el borrador y genera «PDF de prueba» para comprobar la paginación.

«Nombre del bloque en el editor» sirve para identificarlo en la lista. El título impreso se escribe en una línea de la columna central y tiene su propio formato.

### Logos automáticos y persistentes

- **Empresa: configuración del sistema**: usa el logo guardado en «Marca de la Plataforma». Si falta, muestra el nombre configurado.
- **Fabricante: datos del equipo**: usa el logo entregado por el adaptador del equipo. Si falta, muestra el nombre del fabricante.
- **Logo empresa/fabricante guardado en esta plantilla**: sube la imagen una sola vez en los campos del final de las propiedades y elige este origen. El archivo y la referencia quedan guardados; no hay que volver a subirlos por cada PDF.
- Formatos de logo admitidos: PNG, JPG o WebP, hasta 2 MB. Las imágenes se incluyen en el PDF sin depender de enlaces externos.

**Alcance actual:** el proyecto aún no tiene modelos de equipos/fabricantes/tickets. La prueba usa el nombre de fabricante de ejemplo y no simula una relación real de base de datos. Para mostrar un logo de fabricante hoy, guárdalo una vez en la plantilla. Al incorporar Equipos, su consulta podrá proporcionar el logo real y la opción automática lo utilizará sin cambiar el diseño.

### Repetir el encabezado completo

Activa «Repetir este encabezado completo en todas las hojas». Se utiliza el primer bloque de encabezado habilitado con esta opción. Ajusta «Espacio del encabezado repetido (mm)» para reservar espacio suficiente sobre el cuerpo; si agregas muchas líneas, aumenta el valor y comprueba el PDF. La vista rápida muestra el mismo encabezado una vez; las páginas exactas se evalúan en el PDF.

### PDF y permisos

La actualización inicia el trabajador y la limpieza con `www-data`, el usuario del proceso web. El arranque de la aplicación repara propietarios en `storage` y `bootstrap/cache`, incluidos PDF ya generados. Los archivos siguen siendo privados y requieren permiso y pertenencia al usuario que solicitó la prueba.

## Editor con selección y paneles plegables (v11.38.3)

- Al entrar, el menú lateral se muestra compacto. El botón de la esquina superior izquierda permite desplegarlo y volver a colapsarlo.
- «Ocultar bloques» y «Ocultar propiedades» liberan ancho para el informe. Los mismos botones cambian a «Mostrar…» y restauran los paneles. «Ocultar vista previa» también es reversible.
- Nombre/cambio de plantilla y configuración del papel se despliegan desde su cabecera. El listado de bloques tiene su propia flecha.
- Las secciones de propiedades empiezan cerradas: información y distribución, repetición, columnas, logos, variables, fotografías y contenido de texto. Abre solamente lo necesario.

### Cambiar el título impreso sin confundirse con el nombre del bloque

1. Haz clic sobre el título directamente en la vista rápida.
2. Un recuadro azul marca la línea y «Editando: Encabezado · Centro · Línea 1» identifica la selección.
3. En el cuadro superior de propiedades cambia el texto, tamaño de letra, color o negrita.
4. La vista se actualiza; pulsa «Guardar borrador» para conservar los cambios.

Para una línea como «Reporte N.º», puedes elegir «Texto + dato automático», escribir el texto previo y seleccionar la variable del catálogo.

Seleccionar una columna/logo muestra su altura y permite abrir sus propiedades completas. Seleccionar otro bloque muestra el título y un tamaño de letra del bloque (8–22 pt); vacío hereda el tamaño del papel. Los estilos particulares de las líneas del encabezado se controlan por línea.

Los recuadros de selección son exclusivos del editor: no aparecen en el PDF. Los saltos y hojas exactos siguen evaluándose con el PDF de prueba generado en cola.

## Separadores y tabla de datos (v11.38.4)

Se retiró el aviso «PRUEBA · DATOS DE EJEMPLO · NO EMITIDO» de la vista rápida y del PDF, conforme a lo solicitado. Los valores de prueba existentes no se convierten por ello en registros reales.

### Diseño como la referencia

1. En «Nuevo bloque» elige **Título / separador** y pulsa «Agregar bloque».
2. Selecciónalo y cambia el título a `INFORMACIÓN DEL EQUIPO`. El fondo predeterminado es verde turquesa, la letra blanca y la alineación centrada. Ajusta colores y tamaño en los controles rápidos; abre «Franja del separador» para cambiar alineación.
3. Agrega **Datos en columnas (cliente / servicio)**. Se crean dos columnas: `DATOS DEL CLIENTE` e `INFORMACIÓN DEL SERVICIO`.
4. En «Datos en columnas» abre una columna y sus filas. Cada fila permite cambiar etiqueta (por ejemplo `RUC:`), seleccionar el dato automático o escribir un valor manual. Puedes agregar, eliminar y reordenar filas; se admiten una a tres columnas.
5. En «Estilo de los datos» configura colores de títulos, etiquetas y valores. Los bordes empiezan desactivados.
6. Usa las flechas del listado para situar el separador justo encima de la tabla. Los bloques son independientes; puedes reutilizar y ordenar varios separadores.
7. Guarda el borrador y genera un PDF de prueba para comprobar las hojas.

No se insertan bloques automáticamente en tus plantillas guardadas. Oculta o elimina los bloques anteriores que dupliquen los mismos datos, según el diseño que quieras conservar.

### Mostrar u ocultar títulos

- **Mostrar título del bloque**: dentro de «Información y distribución del bloque», para tablas/datos, recomendaciones, texto y firmas. Si colocas un separador arriba, puedes desactivar este título para evitar duplicarlo.
- **Mostrar encabezados de columnas**: para actividades, componentes y repuestos. Oculta los nombres de columnas, conservando las filas y sus anchos.
- **Mostrar títulos de las columnas**: para Datos en columnas. También puedes desactivar «Mostrar este título» en una sola columna.
- **Mostrar etiquetas de los campos**: para ocultar nombres como `Cliente:` o `RUC:` y mantener solamente los valores.
- **Repetir títulos si la tabla continúa**: mantiene los títulos en las hojas siguientes. Los datos se disponen en filas cortas para poder continuar en otra página.

### Fuente y unidades

La familia utilizada es **DejaVu Sans**. Los tamaños se expresan en **puntos (pt)**, adecuados para PDF e impresión: 1 pt equivale a 1/72 de pulgada. En una pantalla con la escala CSS habitual, 12 pt corresponden aproximadamente a 16 px. No se ingresan píxeles en los controles de tamaño. «Tamaño general (pt)» define el tamaño heredado; cada bloque o línea del encabezado puede sobrescribirlo.

## Formato por encabezado, columna y dato (v11.38.5)

En una tabla abre **Formato de encabezados de la tabla** para definir fondo HEX, color de letra, tamaño, fuente, alineación, negrita y cursiva. En **Datos en columnas**, abre la columna que quieras editar (por ejemplo DATOS DEL CLIENTE). Ahí puedes cambiar el título, ocultarlo y abrir **Formato del encabezado de esta columna** para darle otro formato.

Dentro de cada columna abre **Filas** y usa **Añadir dato**. Puedes tener 5 datos en una columna y 7 en otra: cada columna admite entre 1 y 30 filas. Reordena o elimina las filas mediante los controles del listado. La fila contiene **Etiqueta**, **Campo: texto o variable**, y **Dato** o **Valor manual**. Ejemplo: etiqueta `Cliente:` + variable `Cliente → Nombre`. Si el origen es manual, escribe el valor tú mismo. Las variables se resuelven al renderizar; el formato queda guardado en la plantilla.

Abre **Formato de etiquetas de esta columna** o **Formato de valores de esta columna** para aplicar un formato a todos sus datos. Dentro de una fila usa **Formato de esta etiqueta** y **Formato de este valor** para darle tamaño o color propio. Vacío = heredar. El orden de prioridad es tabla → columna → dato. Si eliges «No» en Negrita, se desactiva incluso si la columna está en negrita.

Los tamaños son puntos tipográficos, igual que Word: **12 significa 12 pt**, sin escalas particulares ni conversión a píxeles. Se admiten de 6 a 72 pt y decimales (escribe `10.5`; el navegador puede mostrar coma según su idioma). 1 pt = 1/72 de pulgada. Aumentar el zoom del navegador no cambia el tamaño de impresión. El selector propone tamaños habituales de Word. Las familias incluidas son DejaVu Sans, DejaVu Serif y DejaVu Sans Mono; elegir el mismo tamaño con otra familia puede cambiar el ancho de las letras.

Todos los campos de colores configurables, incluidos Configuración → Colores, usan **HEX #RRGGBB** (ejemplo `#00858B`). Puedes escribir el código o usar el selector HEX de las propiedades. No se admiten RGB, HSL, nombres ni transparencia en colores guardados de la plantilla. Las dependencias de Filament pueden convertir los colores internamente para dibujar su interfaz; los valores editables y almacenados siguen siendo HEX.

Las plantillas anteriores conservan su formato hasta que tú lo cambies. Guarda el borrador y genera **PDF de prueba** para evaluar tamaños y saltos de página. La generación siempre se ejecuta mediante cola.

## Alineación real de cada ítem (v11.38.6)

El formato de texto de v11.38.5 alineaba dentro de la celda de etiqueta o de valor; no movía ambos como un conjunto. Ahora hay un control separado y persistente.

1. Selecciona el bloque **Datos en columnas**.
2. Abre **Datos en columnas**, la columna y su lista **Filas**.
3. Abre una fila. En **Alineación del ítem (etiqueta + valor)** elige **Izquierda**, **Centro** o **Derecha**. Ambos textos se alinean juntos, conservando sus tamaños, fuentes y colores propios.
4. Usa **Sangría del ítem (mm)** para desplazar la fila desde el borde izquierdo. Valor 0 = sin sangría; rango 0–30 mm. Ejemplo 5 = desplazamiento adicional de 5 mm, además del margen interior de la fila de 1 mm.
5. Guarda y genera **PDF de prueba**. La elección queda almacenada en la plantilla y se conserva al volver a abrirla.

Para alinear todas las filas de una columna a la izquierda elige **Alineación de los ítems de esta columna → Izquierda**. Cada fila que tenga **Heredar de la columna** seguirá ese valor. Puedes dar a un ítem otra alineación y no cambia el resto.

Las tablas guardadas antes de esta actualización empiezan con **Diseño anterior: etiqueta y valor en celdas**, para conservar su distribución. Selecciona una alineación nueva por columna o por ítem para usar el conjunto sin espacio fijo de etiqueta. Los bloques nuevos ya empiezan a la izquierda.

La alineación en los paneles de formato de etiqueta/valor controla las celdas cuando se usa el diseño anterior; para mover el conjunto usa el nuevo control de ítem. La sangría del ítem se aplica a izquierda/centro/derecha. Los saltos de página y la generación por cola siguen funcionando.

## Editor compacto y firmas (v11.38.7)

En Datos en columnas ya no necesitas abrir un panel de formato por cada fila. Selecciona la columna con sus botones y el ítem en la lista. También puedes hacer clic en un encabezado o dato del informe: se selecciona su columna/ítem y aparece un recuadro azul que no se imprime.

El panel seleccionado permite cambiar etiqueta, contenido (variable/texto/firma), valor y alineación. **Formato del elemento seleccionado → Aplicar formato a** elige encabezados de tabla, encabezado de columna, etiqueta, valor o firma. Existe un único panel: tamaño en puntos, HEX y negrita; fuente, cursiva, alineación de texto y fondo quedan en **Fuente y formato avanzado**. Una opción vacía hereda el formato. Sin un ítem seleccionado, el formato aplica a la columna; con un ítem seleccionado, a ese dato.

**Tabla: títulos, colores y bordes** y **Columna: título y alineación** están plegados. **Posición y orden** permite sangría, subir/bajar y quitar el ítem seleccionado. Puedes añadir o quitar columnas; se conserva al menos una columna y una fila. Límites actuales: 3 columnas, 30 ítems por columna. **Configuración completa y opciones avanzadas** mantiene todo el formulario anterior (incluidos margen, salto, separación, fuentes, colores y estilos independientes) por si necesitas ajustes adicionales.

### Añadir la firma en Información del servicio

1. Selecciona la columna Información del servicio.
2. En **Nuevo ítem** elige **Firma individual** y pulsa **Añadir ítem**.
3. Cambia la etiqueta, por ejemplo `Firma:`, y la alineación del ítem.
4. En firma elige origen y texto inferior. Abre **Tamaño, línea y nombre de firma** para configurar ancho (15–160 mm), alto (5–60 mm), línea y color HEX, nombre automático o manual.
5. En el panel de formato elige **Firma del ítem** para configurar fuente, tamaño, color, negrita y otras opciones.
6. Guarda y genera el PDF de prueba por cola. Puedes colocar el ítem en cualquier columna y moverlo dentro de su lista.

### Añadir la firma como bloque independiente

En **Nuevo bloque** selecciona **Firma individual**, agrega y coloca el bloque con las flechas. En propiedades abre **Firma**: configura origen, alineación, tamaño, línea, nombre y texto inferior. El título del bloque empieza oculto, pero puedes mostrarlo. Se pueden añadir varios bloques de firma independientes.

**Espacio para firmar en papel** muestra el espacio y línea sin imagen. Los otros orígenes corresponden a la firma del técnico/receptor de este informe: si no se proporciona la imagen, queda el espacio para firmar. Esta actualización añade el campo y su renderizado; no incluye un lienzo para capturar firmas ni conecta registros de firma todavía inexistentes. Las plantillas no guardan ni reutilizan imágenes de firmas. Una firma digital debe capturarse nuevamente para cada informe en el flujo de trabajo que se integre después.

El PDF de prueba usa los datos de ejemplo actuales y los datos reales disponibles (como el técnico o institución elegida). No inventa una firma digital. Las plantillas anteriores se conservan y la firma se añade únicamente cuando tú la agregas.
