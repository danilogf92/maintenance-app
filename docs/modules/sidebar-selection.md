# Selección del menú lateral — v11.33

En escritorio con el menú colapsado, los enlaces usan todo el ancho disponible,
sin padding horizontal, con iconos centrados. El fondo activo y el hover pertenecen
al enlace completo, de modo que el icono no sobresale del área seleccionada.

El menú deja de reservar espacio para una barra inexistente mediante
`scrollbar-gutter:auto`. Si necesita scroll, conserva su barra y permite navegar.
El tamaño de los iconos y la altura mínima de los enlaces se mantienen uniformes.

Las reglas están en `components/layouts/sidebar-state.blade.php`, dentro de la
media query de escritorio y condicionadas por `data-sidebar-collapsed="1"`.
Se aplican antes del primer dibujo. El menú expandido y móvil conserva su diseño.
