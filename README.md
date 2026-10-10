# Maintenance App - base tecnica basada en Corpo Training v0.1.0

Esta entrega utiliza **Corpo_Training_Plantilla_v0.1.0.zip** como plantilla de arquitectura, usuarios, instituciones, roles, marca y administracion. **No incluye tablas SAP, proyectos ni migraciones del antiguo maintenance-app.** La plantilla aun contiene terminologia de capacitacion en algunos documentos y roles, que debera adaptarse en fases posteriores.

## Puertos fijos
- Laravel/nginx: http://localhost:8611
- Filament: http://localhost:8611/admin
- phpMyAdmin: http://localhost:8610
- MariaDB: 127.0.0.1:3611 (dentro Docker: mariadb:3306)

## Windows (Docker Desktop)
1. Extraiga el ZIP en una carpeta nueva. No mezcle con otros proyectos.
2. Abra Docker Desktop.
3. Ejecute **Iniciar.cmd** desde CMD o PowerShell. Antes del arranque se ofrece una limpieza opcional global. **LIMPIAR** elimina contenedores detenidos y TODOS los volumenes sin uso de Docker, incluso de otros proyectos. ENTER omite la limpieza. Para conservar datos antiguos, omitala.
4. Al terminar, cree su propio SuperAdmin (no existe clave predefinida):

```cmd
docker compose exec app php artisan template:setup correo@ejemplo.com
```

5. Acceda a http://localhost:8611/admin

Para volver a iniciar: `Iniciar.cmd`. Para detener sin perder datos: `Detener.cmd`.
Para limpiar huerfanos manualmente: `Limpiar-Huerfanos.cmd`.

**No ejecute `docker compose down -v` ni `docker volume prune -a` si desea conservar bases de datos no respaldadas.**

## Alcance de base
Las migraciones proceden de la plantilla Corpo Training: instituciones, usuarios, configuracion de marca, sesiones, permisos, roles, cache y colas. No hay modulos de mantenimiento implementados todavia.

## Comprobacion
No se ha ejecutado Docker Desktop en este entorno de generacion; el paquete se ha revisado estaticamente. Si el arranque falla, comparta el log completo.
