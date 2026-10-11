# Interfaz y navegación — v11.25

## Inicio, Perfil y Login

Las capturas mostraban clases sin estilos: `page-card`, `page-title`,
`page-input`, `page-button` y los componentes de acceso. El layout ahora carga
hojas CSS directamente desde `public/css/pages/`, después de Vite. No necesita
recompilar los assets para esta actualización.

- `shared.css`: colores, tarjetas, títulos, campos y botones.
- `dashboard.css`: resumen de cuenta y accesos según permisos.
- `profile.css`: datos personales, opciones de tema y guardado.
- `auth.css`: tarjeta de acceso y página invitada con scroll propio.
- `table-navigation.css`: flechas, hover, foco y estabilidad de la tabla.

Las pantallas usan clases explícitas, con versión en las URLs del CSS. Para
cambiar el diseño, edita la hoja de la pantalla; para cambiar colores comunes,
edita las variables en `shared.css`. El color principal sigue usando la paleta
configurada en la plataforma. Incluye estilos para tema oscuro y ancho móvil.

## Navegación entre registros

Antes las flechas hacían `closeForm()` y luego `edit()`, dos peticiones con un
estado intermedio sin editor. Ahora hacen una sola llamada a `edit()`. El
servidor ya guarda y valida el registro pendiente antes de abrir el siguiente;
si falla la validación mantiene el registro actual y muestra sus errores.

Los clics repetidos durante una petición no generan navegación simultánea.
Volver a pulsar la pestaña activa no envía otra petición. Los registros mantienen
sus claves y `wire:ignore.self` conserva el resaltado que gestiona Alpine;
Livewire sigue actualizando las celdas cuando cambian los datos.

El scroll hacia el registro seleccionado usa watchers de `editingId` y
`formOpen`, en lugar de un efecto que también dependía de la animación interna.
Guardar automáticamente un campo no vuelve a desplazar la tabla.

## Qué se conserva

El ancho del editor y los mínimos de v11.24, la confirmación de borrado, la
validación, los permisos y el guardado automático. La pestaña favorita sigue en
la base de datos y se abre al entrar. No se ejecutan seeders ni se borran datos.

## Comprobación

`node tests/Frontend/drawer-resize.cjs` y
`node tests/Frontend/record-navigation.cjs` verifican los controladores reales.
La prueba `RecordNavigationTest` verifica que cambiar directamente de registro
guarda cambios válidos y bloquea el cambio si son inválidos. La compilación de
Blade valida las vistas. No se pudo ejecutar la comprobación visual en navegador
en este entorno porque la descarga del navegador de pruebas devolvió un ZIP vacío.
