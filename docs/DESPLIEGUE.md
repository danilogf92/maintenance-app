# Instalación y despliegue

## Docker local

El instalador crea `.env` desde el ejemplo, construye PHP 8.3, instala el lock de Composer, inicia MariaDB 11.4, ejecuta migraciones/roles y publica el enlace de almacenamiento. phpMyAdmin 5.2 y Nginx 1.28 forman parte de Compose. No hay datos de demostración ni cuentas con contraseña fija.

El primer arranque descarga dependencias y puede tardar varios minutos. Si falla un comando, el script se detiene y deja el destino disponible. Abra CMD en el destino y ejecute `Instalar.cmd` sin parámetro para reintentar allí. Mantenga Docker Desktop abierto.

Puertos locales: aplicación 8080, phpMyAdmin 8081, MariaDB 3307. Los tres se vinculan a 127.0.0.1. Si hay un conflicto cambie el puerto host en `compose.yaml` y `APP_URL` si corresponde; el puerto interno MariaDB sigue siendo 3306.

`DB_HOST=mariadb`, `DB_PORT=3306` para PHP en Docker. `DB_HOST=127.0.0.1`, `DB_PORT=3307` para Herd. No cambie usuario/contraseña del volumen ya inicializado esperando que las variables recrean la cuenta: debe modificarla también en MariaDB.

Los valores del ejemplo son solo para desarrollo local; cambie las contraseñas para instalaciones reales. El volumen de base permanece al detener contenedores. No elimine volúmenes para solucionar un error de instalación con datos existentes.

## PHP/Herd local

Siga README para base en Docker y PHP local. PHP 8.3 recomendado; `composer install` verifica las extensiones y usa las versiones fijadas. No ejecute `composer update` para iniciar el proyecto. No se necesita npm en esta fase.

## Preparar un servidor

El Compose entregado es para desarrollo local. Antes de producción, verifique la fase inicial con MariaDB real y configure un dominio con HTTPS.

1. Instale PHP 8.3 FPM y sus extensiones, Composer 2, Nginx y MariaDB; cree base y usuario con acceso a esa base. Las dependencias y frontend de esta fase no requieren Node.
2. Coloque el código fuera de directorios públicos. Configure Nginx para servir únicamente `<proyecto>/public`; use `docker/nginx.conf` como referencia de rutas, adaptando el socket PHP-FPM al servidor. No copie literalmente `app:9000` para PHP nativo.
3. Cree `.env` privado, con `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://su-dominio`, conexión real a MariaDB, sesiones/caché/cola en base y `SESSION_SECURE_COOKIE=true`.
4. Ejecute `composer install --no-dev --optimize-autoloader`. Dé permiso de escritura al usuario PHP solo en `storage` y `bootstrap/cache`; nunca use 777 para todo el proyecto.
5. En una instalación NUEVA ejecute `php artisan key:generate`, `php artisan migrate --seed --force`, `php artisan storage:link` y `php artisan template:setup su-correo@empresa.com`.
6. Active HTTPS con su certificado e identifique la configuración del proxy si existe. phpMyAdmin y MariaDB deben permanecer privados, accesibles por administración controlada.
7. Ejecute `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`. Compruebe ambas entradas de sesión, creación/edición de personas, desactivación y carga del logo.

En actualizaciones conserve `.env`, `APP_KEY`, la base y `storage/app`. Nunca regenere la clave ni use `migrate:fresh`. Respaldar antes, instalar lock y ejecutar únicamente migraciones nuevas.

La fase 1 no produce trabajos de capacitación: no exige un worker ni scheduler para videos todavía. Se añadirán con FFmpeg y la fase multimedia.

## Respaldo y restauración

Respalde, como mínimo: dump consistente de MariaDB, `storage/app`, `.env` y la versión exacta de código/lock. `.env` contiene secretos: manténgalo privado. El logo está en `storage/app/public/branding`. No basta copiar la carpeta Laravel para respaldar el volumen MariaDB.

Linux, dentro del proyecto Docker, con contenedores iniciados:

```bash
mkdir -p backups
docker compose exec -T mariadb sh -c 'exec mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" --single-transaction "$MARIADB_DATABASE"' > backups/database.sql
tar -czf backups/files.tar.gz storage/app .env composer.lock
```

En Windows use exportación SQL de phpMyAdmin y comprima `storage/app`, `.env` y `composer.lock` con una herramienta local. Copie respaldos fuera del equipo y del contenedor; programar Google Drive no está incluido.

Restaurar en una base NUEVA o previamente respaldada y destinada a la recuperación: importar SQL en phpMyAdmin, restaurar archivos y la misma APP_KEY, ajustar conexiones/URL, reinstalar dependencias y limpiar caché. En Linux puede importar con:

```bash
docker compose exec -T mariadb sh -c 'exec mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' < backups/database.sql
```

Una restauración puede reemplazar datos: comprobar siempre el destino antes. Probar recuperación en una instalación separada es parte de la aceptación operativa pendiente.

## Diagnóstico

- `docker compose ps`: servicios y salud MariaDB.
- `docker compose logs --tail=100 app web mariadb`: errores recientes.
- `docker compose exec app php artisan optimize:clear`: luego de corregir `.env` o configuración.
- Logo ausente: comprobar APP_URL, enlace `public/storage`, archivo y permisos.
- Error 419: cookies/dominio/esquema y tabla de sesiones; no alternar dominios de la misma sesión.
- SQL connection refused: revisar DB_HOST y puerto según Docker/Herd y salud de MariaDB.

No se ha probado físicamente Docker Desktop en Windows ni desplegado este ZIP en un servidor real. Consulte VALIDACION.
