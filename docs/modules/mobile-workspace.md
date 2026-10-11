# Editor móvil — v11.27

El editor de Usuarios e Instituciones se posiciona dentro de
`drawer-workspace`, sobre el área de la tabla. Ya no usa `position:fixed` sobre
toda la pantalla. El encabezado de la aplicación, las pestañas, los filtros y
la búsqueda permanecen accesibles al abrir un registro.

En pantallas de hasta 819 px, `public/css/pages/mobile-workspace.css` distribuye
los controles en tres zonas, como la referencia proporcionada:

1. Encabezado de aplicación y pestañas arriba.
2. Configuración, filtro y búsqueda debajo de las pestañas.
3. Barra inferior con borrar, crear, imprimir y primero/anterior/siguiente/último.

La barra inferior reserva espacio propio en `ops-page`; no tapa los campos ni
el botón Guardar. Incluye `safe-area-inset-bottom` para móviles con indicador
de inicio. Los campos mantienen 16 px de letra para evitar el zoom automático
al editar en iPhone. El editor tiene título, identificador y cierre rojo.

La barra inferior usa los mismos botones, permisos y controlador de navegación
que el escritorio; no se duplican acciones ni se agregan peticiones. Las flechas
pueden abrir registros cuando el editor está cerrado y cambiar de registro
cuando está abierto. Si un campo pendiente es inválido, permanece en el registro
actual. Cambiar de pestaña conserva la validación y el cierre existentes.

Los diálogos de confirmación de borrado y configuración siguen siendo diálogos
que bloquean temporalmente otras acciones. Esta distribución corresponde al
editor del registro, que debe permitir navegar.

Para ajustar el móvil, edita `mobile-workspace.css`. Las clases
`ops-record-delete`, `ops-record-create`, `ops-record-print` identifican las
acciones; las reglas de escritorio no cambian. La posición del editor se define
en `livewire/user/form.blade.php` y `livewire/institution/form.blade.php`.

Se verificaron las vistas y los controladores existentes de navegación,
persistencia del ancho y menú lateral. Queda pendiente la comprobación visual
y táctil en el navegador del teléfono.
