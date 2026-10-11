# Editor compartido

| Pieza | Responsabilidad |
|---|---|
| app/Livewire/Shared/RecordEditor.php | Abrir, cerrar, crear, cargar y guardar cambios pendientes |
| app/Livewire/Shared/SavesRecords.php | Creación automática y autoguardado del registro vigente |
| app/Livewire/Shared/ConfirmsRecordDeletion.php | Solicitar, cancelar y ejecutar la confirmación |
| app/Livewire/Shared/HasRecordViews.php | Pestañas y columnas, limitadas al usuario y módulo |
| app/Livewire/Shared/ConfiguresRecordColumns.php | Validación, orden y visibilidad de columnas |
| components/record-editor/drawer-workspace | Resize, mínimos, persistencia y seguimiento de fila |
| components/record-editor/record-navigation | Primero, anterior, siguiente y último según filas filtradas |
| components/record-editor/record-form | Espera de 700 ms, envío y estado de guardado |
| components/record-editor/record-save-status | Indicador visual |
| components/record-editor/drawer-handle y drawer-close | Tirador y X roja |
| components/record-editor/view-settings | Configuración de columnas |
| components/record-editor/delete-confirmation | Confirmación de borrar registro abierto |

Los componentes Blade están en `resources/views/components/record-editor/`. Las páginas UserPage e InstitutionPage son ejemplos del contrato: implementan modelo, campos, carga, reinicio, consulta, guardado y borrado; heredan el comportamiento común.

## Flujo

Al abrir otro registro o cambiar de pestaña se guardan primero los cambios pendientes del registro actual, nuevo o existente. Una validación fallida conserva la ficha y sus errores. Un registro nuevo se crea automáticamente al pasar la validación; un formulario nuevo intacto no crea una fila. El autoguardado verifica el ID para rechazar solicitudes tardías de otra ficha. La contraseña se limpia después de autoguardar.

El servidor controla `editingId`, `formOpen`, `recordDirty`, `editorSession`, `currentViewId` y el estado de confirmación mediante `#[Locked]`. Cada petición vuelve a comprobar el permiso del módulo en `boot()`, y los servicios autorizan antes de escribir. Los métodos de persistencia/borrado son protegidos; no hay una acción pública `deleteSelected` que salte la confirmación. La confirmación evita errores accidentales; una persona autorizada todavía puede llamar solicitar y confirmar programáticamente.

El resize guarda ancho en localStorage en cada movimiento. Formulario mínimo 420 px y tabla mínima 400 px; por debajo de 820 px ocupa la zona de la tabla y conserva la navegación. Se conserva la clave de localStorage y los nombres de módulos de las vistas guardadas, por lo que esta actualización mantiene tamaños y pestañas existentes.

Para nuevos módulos, añade una página, su servicio y vistas propios, y reutiliza estos componentes. No dupliques las reglas de seguridad en Blade.

Favorita al entrar: consulta [favorite-view.md](favorite-view.md). Estrategia de caché: [../performance/cache-strategy.md](../performance/cache-strategy.md).

Creación automática: consulta [automatic-creation.md](automatic-creation.md).
