# Módulo User — Usuarios

Empieza por `app/Livewire/User/UserPage.php`. Esta clase contiene el estado de la pantalla y delega las reglas; no ejecuta SQL de guardado ni decide roles por su cuenta.

| Archivo | Cambiar aquí |
|---|---|
| app/Modules/User/UserData.php | Campos permitidos y validación |
| app/Modules/User/UserAccess.php | Cuentas protegidas y roles asignables |
| app/Modules/User/UserManager.php | Guardado en transacción y sincronización de roles |
| app/Modules/User/DeleteUser.php | Reglas de borrado |
| app/Modules/User/UserQuery.php | Búsqueda, filtros, relaciones y orden |
| app/Modules/User/UserDefinition.php | Columnas disponibles y pestañas iniciales |
| routes/modules/user.php | Ruta de la pantalla |
| resources/views/livewire/user/index.blade.php | Composición general |
| resources/views/livewire/user/toolbar.blade.php | Acciones y pestañas |
| resources/views/livewire/user/filters.blade.php | Filtros |
| resources/views/livewire/user/table.blade.php | Filas y representación de columnas |
| resources/views/livewire/user/form.blade.php | Cabecera, cierre y contenedor de campos |
| resources/views/livewire/user/fields.blade.php | Inputs y errores |

Para añadir un campo: revisa primero la migración y `App\Models\User`, añádelo a `UserData::FIELDS` y su validación; decláralo/cárgalo en `UserPage`; añade el input. Si debe verse en la tabla, añade la definición y su caso en `table.blade.php`. No añadas contraseñas ni tokens como columnas.

`App\Models\User` conserva su nombre porque Spatie guarda ese tipo en las tablas de roles/permisos. Moverlo requeriría una migración de relaciones; esta entrega reorganiza el módulo sin cambiar esa identidad.

La URL `/operations/users` y el nombre `operations.users` se conservan para los enlaces existentes. El código del módulo ya no vive en Operations. Los permisos siguen siendo globales, no existe aislamiento por institución.

El panel Filament usa el mismo `UserManager` y `UserAccess`: modificar reglas en un único lugar afecta ambos formularios. Las cuentas con permisos administrativos se protegen aunque tengan `can_access_admin=false`. Solo SuperAdmin puede modificarlas o asignar privilegios administrativos. Nadie puede borrarlas desde este editor ni borrarse a sí mismo.
