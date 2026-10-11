# Fase 1 — Base del sistema, mobile first

Se parte de la plantilla Corpo Training. No se agregan módulos de mantenimiento ni cursos antes de definir sus requisitos.

## Entregado
- Laravel 12, Livewire 3, Filament 4, usuarios, instituciones, roles, permisos, SuperAdmin y marca de la plantilla.
- Layout público/participante mobile-first: barra superior, hamburguesa móvil, menú horizontal en escritorio, contraste y foco de teclado.
- Dashboard responsive y acceso al panel de administración condicionado a permisos.
- Perfil `/profile`: nombre y preferencia de tema `system|light|dark` persistida en `users.theme_preference`.
- CSS independiente de Tailwind/Vite; no requiere npm para estas pantallas.
- Migración **aditiva** para tema, sin modificar migraciones anteriores ni borrar datos.

## Instalación nueva
Extraer en carpeta nueva; ejecutar `Iniciar.cmd`. Para crear el primer SuperAdmin, usar `docker compose exec app php artisan template:setup correo@ejemplo.com`.

## Actualización de instalación existente
1. Respaldar proyecto y base de datos. No copiar `.env` del ZIP encima del existente.
2. Copiar los archivos del parche de esta versión conservando `.env`, `storage`, `vendor` y volúmenes Docker.
3. Ejecutar `docker compose up -d --build` y `docker compose exec app php artisan migrate --force`.
4. Verificar `/login`, `/dashboard`, `/profile`, `/admin` en móvil y escritorio.

**No ejecutar `docker compose down -v` ni `migrate:fresh`.** `Limpiar-Huerfanos.cmd` puede afectar otros proyectos: su uso es opcional.

## Pendiente de validar en máquina Windows
Inicio Docker real, instalación Composer, acceso a MariaDB, Filament y pruebas manuales de pantallas a 360, 390, 768 y 1280 px.
