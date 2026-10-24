# Optimización sin paginación — v11.50.3

## Comportamiento conservado

Las tablas principales muestran todos los registros autorizados que coinciden con la vista y la búsqueda. Esta actualización no añade paginación, recortes de filas, carga progresiva ni controles de página. Conserva los filtros de columna, el orden, la agrupación, la navegación entre registros y la pestaña seleccionada.

## Cambios de rendimiento

- El cálculo de filtros, opciones de columna, orden y agrupación reutiliza los valores de cada registro durante esa operación. Evita resolver una misma propiedad o relación repetidamente durante las comparaciones de orden. No conserva esos valores entre peticiones ni omite registros.
- Sin filtros activos, no se crea una copia filtrada de la colección.
- Las versiones de caché se leen una sola vez por petición y se invalidan al guardar. Las comprobaciones de permisos siguen ejecutándose en el servidor.
- Usuarios no consulta opciones de roles e instituciones cuando su editor está cerrado; se cargan al abrirlo.
- PHP-FPM utiliza OPcache y caché de rutas reales. Se comprueban cambios manuales de código cada dos segundos; el actualizador reinicia los procesos.
- La caché de datos, las vistas compiladas y bootstrap/cache usan volúmenes Docker internos compartidos por aplicación y workers, reduciendo operaciones en el montaje Windows/OneDrive.
- El arranque deja de recorrer todos los archivos subidos para cambiar permisos en cada comando. Conserva los permisos de ejecución y prepara los directorios necesarios.
- Docker excluye storage y respaldos del contexto de construcción. Los archivos subidos permanecen disponibles mediante su montaje existente.
- El actualizador reconstruye las cachés de configuración, rutas, vistas y componentes de Filament. Nginx comprime respuestas y permite reutilizar recursos con hash de public/build.
- Se corrige una entrada mal formada del almacén failover de caché sin cambiar el CACHE_STORE configurado.

## Mediciones y validación

Prueba aislada del procesamiento de una tabla con 5.000 instituciones, repetida cinco veces con idénticos filtros, orden y agrupación: aproximadamente 1.319 ms antes y 261 ms después. Ambas ejecuciones devolvieron los mismos 3.334 registros que cumplen el filtro y el mismo orden, comprobado mediante hash de identificadores. Esta medición corresponde al algoritmo de preparación de tabla; no representa la carga completa de una página ni la velocidad de Windows.

Una prueba HTTP comprueba que una lista con más de 500 instituciones contiene una fila HTML por cada institución, sin controles de paginación. La revisión en navegador comprueba todos los registros juntos y la conservación de pestañas al usar las flechas de navegación.

Las mediciones se realizan con PHP 8.3 y SQLite en un entorno Linux aislado. No se ha ejecutado Docker Desktop en el equipo Windows del usuario. El diagnóstico automático de la instalación permite comprobar esa configuración real.

## Estado del ZIP recibido

El código de maintenance-app(5).zip coincide con v11.50.0. No contiene la interfaz de Contactos v11.50.1. Esta entrega incluye esa interfaz y las optimizaciones sin requerir instalar versiones intermedias.

## Actualización

Extrae el bundle fuera del proyecto y ejecuta Actualizar.cmd con la ruta de Maintenance APP. El instalador valida la identidad y los servicios antes de detener o copiar. Conserva .env, APP_KEY, base de datos, archivos subidos y sesiones. No crea respaldos ni requiere Redis.

El instalador no corresponde a constructora-app. Este proyecto mantiene los servicios app, web, mariadb, reports-worker y reports-scheduler. La generación de PDF permanece siempre en la cola reports.

Al terminar se ejecuta `php artisan maintenance:diagnose` y se guarda un resultado sin credenciales en storage/logs/performance-check.json. Incluye versión de código, conexión, configuración de OPcache y tiempos de escritura/lectura de caché. La comprobación de OPcache desde CLI describe la extensión y configuración, no el hit rate del proceso web.

La primera reconstrucción puede tardar por la instalación de extensiones de PHP. La versión se marca como completa solo cuando finalizan todos los pasos. Si ocurre un error, conserva la salida del comando para revisarla; no hay rollback automático de migraciones.

## Dónde modificar

- Preparación de tablas: app/Modules/Shared/RecordTable.php.
- Caché de datos: app/Support/Cache/PageDataCache.php.
- PHP-FPM: docker/performance.ini.
- Cachés internas: compose.yaml.
- Arranque: docker/entrypoint.sh.
- Diagnóstico: app/Console/Commands/DiagnosePerformance.php.
