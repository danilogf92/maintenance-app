# Validación v0.1.0

## Ejecutado en esta entrega

- Dependencias instaladas desde composer.lock, con dependencias innecesarias retiradas y actualizaciones de seguridad dentro de las mismas versiones principales.
- PHP CLI 8.3.6, Laravel 12.69.3, Livewire 3.8.10, Filament 4.15.1, Spatie Laravel Permission 8.3.0.
- Suite SQLite: **18 pruebas, 61 comprobaciones, todas aprobadas**.
- Ingreso/salida, cuenta desactivada, revocación de sesión existente, autorización del panel, recursos administrativos renderizados, asignaciones múltiples, contraseña conservada/hasheada, protección de privilegios, formulario real de creación/edición Filament y configuración inicial.
- Formato PHP aplicado con Laravel Pint.
- Auditoría final Composer: cero avisos de vulnerabilidad y cero paquetes abandonados en la consulta de esta entrega. Esto no sustituye una auditoría completa de la aplicación.
- `composer validate --no-check-publish`, cache de rutas y compilación/cache de vistas Blade.
- Sintaxis Bash del instalador; inspección de configuración Docker y YAML.

Estas cifras corresponden a este proyecto; las pruebas del historial antiguo pertenecen a otra entrega.

## Pendiente en el entorno del usuario

- Docker build/Compose realmente ejecutados; aquí no hay Docker disponible.
- Pruebas contra MariaDB 11.4, incluyendo bloqueo/transacciones y comportamiento concurrente.
- Instalador CMD/PowerShell ejecutado en Windows; aquí no hay PowerShell disponible.
- Recorrido visual completo, carga de logo y navegadores/móviles del usuario.
- Despliegue con HTTPS, prueba de carga y restauración de respaldo.

Drive, HLS, cursos, límites de sesión y evaluaciones son funciones por implementar, no pruebas pendientes de código ya presente.

## Verificación local recomendada

1. Instalar en una carpeta nueva con Instalar.cmd.
2. Crear SuperAdmin por `template:setup` y acceder al panel.
3. Crear institución, usuario Cliente y usuario Administrador con acceso administrativo.
4. Ingresar como Cliente: panel bloqueado. Administrador: usuarios e instituciones disponibles; roles y marca bloqueados.
5. Como SuperAdmin, cambiar nombre/logo y comprobar login/aplicación/panel tras recargar.
6. Ejecutar `docker compose exec app php artisan test`.
7. Desactivar una cuenta y comprobar ingreso rechazado.

Los tests usan SQLite `:memory:` configurado en phpunit.xml. No ejecutar migraciones de reconstrucción sobre una base real para “probar”.
