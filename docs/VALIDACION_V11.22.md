# Validación v11.22

La entrega se valida con PHP 8.3.6, dependencias instaladas desde el composer.lock recibido y SQLite :memory:. No se actualizan versiones.

- Suite de aplicación y módulos: **36 pruebas, 96 comprobaciones, todas aprobadas**.
- Casos: login/logout, inactividad, autorización del panel, creación/edición Filament, privilegios, roles web, borrado confirmado/cancelado, propiedades Locked, aislamiento de vistas, autoguardado tardío/cero, guardado antes de cerrar y validación al cambiar pestaña.
- Caché de rutas y compilación de todas las vistas Blade: correctas.
- PHP lint: 82 archivos propios, ningún error.
- CSS dividido en módulos manteniendo concatenación exacta de las reglas originales y su orden.
- Composer audit --locked: 0 avisos y 0 paquetes abandonados en la consulta.

Pendientes: ejecución del instalador CMD/PowerShell en Windows, recorrido visual, Docker/MariaDB y concurrencia. La actualización ejecuta verificaciones y tests aislados en tu servicio app.

Los tests usan base SQLite en memoria, sesión/caché array y clave de prueba. El comando de actualización no ejecuta migraciones ni seeders sobre la base de la aplicación. Los seeders de los tests solo afectan SQLite en memoria.
