# Revisión de código — v11.22

Fecha: 10/10/2026. Base: maintenance-app(2).zip entregado por el usuario. Revisión del código propio de aplicación y controles de despliegue; dependencias evaluadas mediante composer.lock y Composer audit. No es una prueba de penetración del servidor del usuario.

## Hallazgos y cambios

| Riesgo | Evidencia en la base recibida | Resultado |
|---|---|---|
| Alto: modificación de cuenta privilegiada | UserManager protegía SuperAdmin y can_access_admin, pero no una cuenta con permisos directos y bandera false; un gestor users.manage podía cambiar su contraseña y conservar esos permisos. También podía modificar/demover otra cuenta Administrador enviando roles ordinarios | UserAccess protege también permisos administrativos directos/heredados. Prueba que rechaza esa modificación |
| Medio: saltar el diálogo de borrado | deleteSelected era público y selectedIds/confirmDelete eran manipulables por cliente; permitía a un usuario autorizado borrar sin el flujo de confirmación | Eliminado ese método; objetivo y confirmación Locked; servicios autorizan y protegen cuentas/relaciones |
| Bajo: IDs de roles de otro guard | La validación exists no restringía guard_name; podría producir una excepción al sincronizar roles | Validación y consulta restringidas a web; caso probado |
| Bajo: formato de color sin validar | ColorPicker solo required; cadenas usadas en CSS sin lista de formato | Regex hexadecimal en formulario y fallback en render. No se ha demostrado XSS explotable: las columnas MariaDB ya limitan a 7 caracteres y Blade escapa HTML |
| Integridad: teléfono más largo que columna | Instituciones validaba hasta 80 caracteres; la columna admite 40 | Una regla compartida de 40 en el servicio, aplicada a ambos formularios |
| Integridad: cierre antes del autoguardado | El cierre anterior cancelaba sin guardar cambios pendientes | Se guardan antes del cierre/cambio; errores de validación conservan la ficha |
| Integridad: autoguardado con ID cero | Con ficha nueva, null convertido a 0 podía superar la comparación del ID | Autoguardado exige ID existente positivo; caso probado |

La confirmación protege el flujo de interfaz, no sustituye permisos. Una persona autorizada puede llamar solicitar y confirmar programáticamente. Los permisos de administración son globales; la institución no es una frontera entre clientes.

## Controles comprobados

- Login limitado a 6 intentos/minuto; sesión regenerada al ingresar e invalidada al salir.
- Cuenta desactivada rechazada al ingresar y en sesiones existentes.
- Middleware web/CSRF de Laravel activo; no excepciones propias agregadas.
- Autorización del módulo en cada petición Livewire y nuevamente en los servicios de escritura.
- Identidad del registro/vista y estado de confirmación Locked; vistas consultadas por usuario y módulo.
- Contraseñas con cast hashed, ocultas en serialización del modelo; no se muestran como columna.
- Guardado restringido a campos validados; filtros con consultas Eloquent parametrizadas.
- Roles/Marca/Colores restringidos a SuperAdmin; rol reservado protegido.
- Se añade canEdit explícito en recursos como defensa adicional. No se reporta un bypass de rutas de Filament: su viewAny ya bloquea las páginas del recurso.
- Logo limitado a PNG/JPEG/WebP y 2 MB. Nginx sirve public, bloquea PHP ajeno a index.php y rutas ocultas.
- Puertos de Docker unidos a 127.0.0.1 en el archivo recibido.
- Composer audit --locked devolvió advisories=[], abandoned=[] durante esta revisión; se conserva composer.lock sin actualizar versiones.

## Límites y pendientes

- No se accedió al .env real, usuarios reales, logs del servidor ni configuración del host. El ZIP no incluyó .env.
- MariaDB y concurrencia de transacciones deben comprobarse en el entorno Docker del usuario; la suite ejecutada usa SQLite en memoria.
- No se ejecutó el instalador Windows/Docker ni una revisión visual interactiva del navegador aquí.
- Si se publica fuera del entorno local: APP_DEBUG=false, HTTPS y cookies secure; revisar secretos y exposición de phpMyAdmin. No se modifica el .env del usuario.
- No hay 2FA, recuperación de contraseña, historial de auditoría ni límites de sesiones en esta fase.
- Las tablas cargan todos los resultados. A 500 registros permiten navegar primero/último; para volúmenes mayores hace falta paginación con navegación definida por consulta, no solo por filas del DOM.
- Los tests de palette usan SQLite, que no aplica el límite VARCHAR de MariaDB. Verifican sanitización del renderer, no prueban que MariaDB permita guardar el payload largo.

## Referencias oficiales

- https://livewire.laravel.com/docs/3.x/security
- https://livewire.laravel.com/docs/3.x/locked
- https://filamentphp.com/docs/4.x/resources/overview#authorization
- https://getcomposer.org/doc/03-cli.md#audit

Consulta `../VALIDACION_V11.22.md` para resultados y `../modules/` para modificar el código.
