# Continuar el desarrollo

Proyecto: Corpo Training. Base: plantilla v0.1.0. Conservar versiones fijadas salvo razón concreta, registros, APP_KEY y archivos. Esta base reemplaza el punto de partida para nuevos desarrollos; no modifica projectsIA.

En la próxima conversación adjunte el ZIP de esta versión o el proyecto instalado actualizado. Pida: “Desarrolla los ítems 2.1 y 2.2 del plan, conservando la base y entregando un ZIP con el código y actualización incremental para Windows”.

Leer README, FASES, ARQUITECTURA, VALIDACION y el historial de referencia. Inspeccionar el código real antes de cambiarlo. No asumir que las funciones del manual antiguo ya están implementadas aquí.

Formato esperado de futuras actualizaciones:

- Versión origen/destino y manifiesto con hashes de los archivos reemplazados.
- Instalador CMD/PowerShell con parámetro de ruta al proyecto.
- Verificar versión y archivos destino antes de copiar; si hay cambios locales incompatibles, detener e informar el conflicto.
- Respaldar los archivos reemplazados; nunca incluir `.env`, `APP_KEY`, vendor, archivos subidos, base de datos o logs del autor.
- Migraciones nuevas para cambios de esquema; prohibido reconstruir la base con migrate:fresh en instalaciones existentes.
- Composer install sobre lock actualizado cuando cambien dependencias; limpiar cachés y explicar cualquier despliegue adicional.
- Reportar pruebas reales y pendientes; no llamar probados a Docker, Windows, Drive o producción sin haberlos ejecutado.

En esta entrega el instalador copia a una carpeta nueva. Aún no existe un parche para actualizar una instalación anterior: se añadirá al producir la primera versión incremental.
