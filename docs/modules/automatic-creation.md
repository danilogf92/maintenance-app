# Creación y guardado automáticos — v11.30

Pulsa `+`, completa los campos y espera aproximadamente 700 ms después del
último cambio. El editor crea el registro cuando pasa la validación y mantiene
la ventana abierta. Los cambios siguientes actualizan ese mismo registro.
No hay botón Guardar en Usuarios ni Instituciones. El visto verde indica que
se guardó; los errores se muestran en el formulario y bajo cada campo.

Usuarios requiere nombre, correo único, institución y contraseña inicial de
al menos 12 caracteres. Instituciones requiere nombre, código único y tipo.
No se insertan cuentas vacías, contraseñas inventadas ni instituciones inválidas.
Un formulario nuevo sin completar sigue siendo un borrador en el editor.

Antes, `record-form.blade.php` detenía el guardado si `editingId` era nulo. Ahora
usa `autosaveDraft` para nuevos y `autosaveRecord` para existentes. Ambos pasan
por los servicios de validación y permisos del módulo. El estado de guardado
se asocia a `editorSession`, un UUID bloqueado que cambia al abrir otro editor.
Asignar un ID a un registro recién creado no cancela cambios que se escriban
mientras se completa la petición.

La nueva tabla `operation_record_creations` registra usuario, módulo, token e
ID creado. Su clave única y transacción hacen que una petición repetida para
el mismo borrador recupere el mismo registro. Si la validación falla se revierte
la transacción; si el registro ya fue eliminado no se vuelve a crear. El token
queda como referencia para evitar reinserciones tardías. No contiene campos del
formulario ni contraseñas. El usuario y módulo siempre proceden del servidor.

Al crear se limpia la búsqueda textual para no ocultar el registro nuevo detrás
del texto anterior. Los filtros de la vista siguen vigentes. Si cambias de
registro o cierras antes del debounce, el servidor guarda un borrador válido;
al cambiar de registro, si tiene errores mantiene el editor abierto y los muestra.
Desde v11.31, la X y Escape permiten cerrar y descartar un borrador nuevo incompleto.
Los borradores válidos se guardan antes de cerrar. Cerrar un formulario nuevo
intacto no crea registros. Ver `editor-visibility.md`.

## Dónde cambiarlo

- `Livewire/Shared/SavesRecords.php`: rutas de guardado y estado del registro.
- `Modules/Shared/RecordCreation.php`: creación única por borrador.
- `components/record-editor/record-form.blade.php`: debounce y cola de cambios.
- `components/record-editor/validation-summary.blade.php`: errores visibles.
- `Modules/User/UserData.php` y `Modules/Institution/InstitutionManager.php`:
  validación de cada módulo.

El instalador aplica solo las migraciones específicas de preferencias y creación
única. No cambia datos existentes ni ejecuta seeders. La tabla adicional evita
duplicados sin introducir una caché para los registros o las favoritas.

Las pruebas `AutomaticCreationTest` cubren creación, actualización, borradores
inválidos, peticiones repetidas y cierre rápido. `automatic-save.cjs` cubre el
controlador, incluidos cambios pendientes durante la creación.
