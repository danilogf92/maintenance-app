# Menú lateral estable — v11.26

El estado colapsado se restauraba al inicializar Alpine, después del primer
dibujo de la página. El HTML inicial mostraba 244 px y luego cambiaba a 68 px;
`transition-all` hacía visible esa apertura y cierre al seguir un enlace.

Ahora `components/layouts/sidebar-state.blade.php`, incluido en el `head` antes
de Vite, lee la preferencia y establece `data-sidebar-collapsed` en `html`.
Sus reglas CSS aplican desde el primer dibujo el ancho del menú, el espacio del
contenido, la visibilidad de los textos y la flecha del botón. Alpine toma ese
mismo estado. El botón actualiza a la vez el atributo y la preferencia.

No hay animación de ancho al cargar o cambiar de página. El estado expandido
también se conserva. En móvil sigue funcionando la apertura mediante el botón
de menú; las reglas de ancho se limitan a pantallas de al menos 1024 px.

El estado lateral usa la clave existente `maintenance-sidebar-collapsed` del
navegador. No cambia la pestaña favorita, que sigue en la base de datos.

Para cambiar las medidas, edita `--sidebar-width` en el componente. El ancho y
el espacio del contenido usan la misma variable para mantenerse sincronizados.
`node tests/Frontend/sidebar-state.cjs` verifica la restauración antes del
contenido, persistencia al navegar, botón y recuperación si se bloquea el
almacenamiento. La verificación visual queda pendiente en el navegador local.
