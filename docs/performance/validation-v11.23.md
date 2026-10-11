# Validación v11.23

- Suite de aplicación, seguridad, caché y favorita: **48 pruebas, 131 comprobaciones aprobadas**.
- Caché funcional: editar datos actualiza listas; asignar roles invalida; crear usuario actualiza contador de institución; cambios externos se reflejan al reingresar o vencer TTL; marca y paleta se actualizan al editar.
- Favorita: una preferencia por usuario/módulo; se abre al entrar; no permite elegir vistas ajenas; al borrar una favorita personalizada recupera la primera existente.
- Benchmark final con driver file en directorio temporal aislado, SQLite :memory:, PHP 8.3.6 y 500 usuarios + 500 instituciones: primera carga **9 consultas / 54,84 ms**; caché caliente **0 consultas / 12,72 ms**. Escenario de carga de datos, sin render Blade, red ni DOM. Los tiempos son orientativos de este entorno, no del Windows/Docker del usuario.
- Sintaxis PHP: 92 archivos comprobados, sin errores.
- Caché de rutas y compilación de todas las vistas Blade: correctas.
- Código propio entregado: máximo 300 líneas por archivo.
- Instalador revisado; aplicación de archivos y compatibilidad verificadas contra las bases v11.21 recibida y v11.22.

Pendientes en el equipo del usuario: CMD/PowerShell/Docker, MariaDB y concurrencia real; revisión visual con hover/foco en navegador. No fue posible instalar el navegador de pruebas aquí; la compilación y las pruebas de render Blade sí pasaron.

El instalador aplica solo la migración de preferencias, preserva registros y ejecuta pruebas con SQLite en memoria. Los tests usan caché array; el benchmark usa archivos temporales propios que elimina al terminar. No se calienta ni invalida la caché persistente de la aplicación desde la suite.
