# Menú lateral — v11.48.2

Catálogos de equipos tiene un control visible de abrir/cerrar. La flecha apunta a la derecha cuando está cerrado y gira hacia abajo cuando se abre. Los submenús usan sangría y una línea de guía; la página actual tiene un fondo más marcado y una barra lateral. El grupo padre también señala que contiene la página actual.

Los enlaces y grupos muestran foco al navegar con teclado y respuesta al pulsarlos. Enter y Espacio abren o cierran el grupo. Se recuerda su estado por usuario en este navegador, independientemente del menú lateral compacto. La versión móvil mantiene el indicador y cierra el menú al elegir una página. El tema oscuro conserva contraste y se respeta la preferencia de reducir animaciones.

El componente reutilizable está en resources/views/components/layouts/nav-group.blade.php. Recibe id, label, icon y active, con los enlaces en su contenido. Los estilos están en public/css/pages/sidebar-feedback.css y se cargan desde components/layouts/app.blade.php. No necesita recompilar los recursos ni añadir dependencias.

Los permisos continúan controlando si el grupo y sus enlaces se muestran. No se cambian las rutas, los datos ni la navegación entre registros de v11.48.1. No hay migraciones nuevas.
