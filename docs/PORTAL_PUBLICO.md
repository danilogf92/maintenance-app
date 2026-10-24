# Presentación pública y acceso

La raíz `/` muestra la presentación antes de iniciar sesión. `/login` conserva el acceso por correo y contraseña, la validación de cuentas activas y la limitación de intentos. Las rutas operativas siguen protegidas. Un usuario autenticado puede visitar la presentación y dispone de «Ir al panel».

## Dónde cambiar cada elemento

- Nombre y logo: configuración de marca existente en Admin (`BrandSetting`). El portal lee los mismos valores de la base de datos; si no hay logo utiliza el símbolo de Maintenance APP.
- Título, descripción y capacidades: `config/portal.php`.
- Presentación y secciones: `resources/views/public/welcome.blade.php`.
- Esquema de operación: `resources/views/public/partials/operation-map.blade.php`. Es ilustrativo, sin consultas ni datos de clientes.
- Cabecera, pie y metadatos: `resources/views/components/layouts/public.blade.php`.
- Formulario de ingreso: `resources/views/auth/login.blade.php`.
- Colores HEX, espaciado, tipografía y adaptación móvil: `public/css/pages/portal.css`. Se utiliza tipografía de sistema, sin fuentes externas.
- Mostrar/ocultar contraseña: `public/js/public-access.js`. Sin JavaScript, el formulario sigue funcionando y no aparece el botón inactivo.

Los enlaces de presentación llevan a secciones de la misma página. Los botones de acceso llevan al login o al panel si ya hay sesión. No hay registro público ni mecanismos de recuperación nuevos; se indica contactar al administrador. No se publican estadísticas, nombres, equipos o información de usuarios reales.

El diseño público tiene identidad clara fija y es independiente del tema personal del panel. No necesita recompilar Vite ni instalar dependencias. Al editar configuración en producción ejecuta `php artisan optimize:clear` dentro del contenedor; el actualizador ya limpia las cachés.
