# Manual de la fase 1

## 1. Ingreso

Abra `/login`. Introduzca el correo y contraseña creados por el administrador. Las cuentas desactivadas no ingresan. Tras seis solicitudes por minuto desde la misma dirección, el ingreso normal se limita temporalmente.

**Captura pendiente:** formulario completo, sin escribir una contraseña visible.

## 2. Aplicación normal

`/dashboard` muestra su nombre, institución y roles. El enlace Administración aparece solo con acceso autorizado. “Salir” cierra la sesión. El espacio de cursos anuncia que se incorporará en una fase posterior.

**Captura pendiente:** inicio con un usuario ordinario y otro administrativo para comparar navegación.

## 3. Administración

Entre en `/admin` o `/admin/login`. La sesión es compartida con la aplicación. “Volver a la aplicación” permite regresar. El nombre y logo son los mismos que en el ingreso normal.

**Captura pendiente:** panel y menú lateral del SuperAdmin.

## 4. Instituciones

Entre en Instituciones. Cree nombre, código único y tipo; por ejemplo, una universidad o clínica. El listado muestra cuántos usuarios pertenecen a cada una. Puede editar. No hay eliminación en esta entrega. No cambie institución para representar una inscripción histórica: esa información se guardará separadamente al implementar las ediciones.

**Captura pendiente:** lista y formulario de institución.

## 5. Usuarios

Abra Usuarios → crear. Introduzca nombre, correo, institución, datos personales opcionales y contraseña (12 caracteres como mínimo). El cargo es informativo. Seleccione uno o varios roles. Active la cuenta.

El SuperAdmin puede habilitar acceso administrativo; además debe asignar un rol que incluya `panel.access` (por ejemplo Administrador). Una persona con solo Técnico o Cliente tendrá acceso normal. El Administrador puede crear cuentas normales y asignar roles sin permisos administrativos.

Al editar, **deje la contraseña vacía para conservarla**. Para cambiarla, escriba una nueva de al menos 12 caracteres. Para revocar acceso, desactive la persona. Una cuenta no puede desactivarse a sí misma por este formulario. No retire el último SuperAdmin activo.

**Capturas pendientes:** listado, formulario de creación y edición con contraseña vacía; error al intentar desactivar la propia cuenta.

## 6. Roles (SuperAdmin)

Los roles combinan permisos. Cree un rol y seleccione sus permisos. Todos utilizan el guard `web`. SuperAdmin es reservado; se asigna a usuarios desde su formulario pero no se modifica en el catálogo. No conceda permisos administrativos a un rol de participantes si no desea que deje de ser un rol ordinario.

**Captura pendiente:** lista y edición de un rol diferente a SuperAdmin.

## 7. Marca de la plataforma (SuperAdmin)

Abra Marca de la plataforma → editar. Cambie el nombre y suba un PNG, JPEG o WebP de hasta 2 MB. Guarde y recargue la página. La siguiente carga aplica la marca a ingreso, aplicación y panel. Se requiere `php artisan storage:link` y `APP_URL` correcto. Los logos son públicos; no utilice allí documentos confidenciales.

**Capturas pendientes:** formulario y las tres pantallas mostrando la marca guardada.

## 8. Qué sigue

El límite de dispositivos y sesiones, cursos, editor, videos y evaluaciones no forman parte de esta versión. Consulte `FASES.md`. El historial de referencia conserva las decisiones de esas funciones para desarrollarlas sin repetir el levantamiento.
