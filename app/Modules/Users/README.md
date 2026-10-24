# Usuarios: presentación uniforme

Usuarios usa el formato visual de Instituciones dentro del área normal. `/admin` conserva su administración independiente.

- `Configuration/Fields.php`: etiquetas, controles y campos exclusivos de SuperAdmin.
- `Configuration/Tabs.php`: pestañas, secciones, orden y anchura de cada campo.
- `Configuration/Layout.php`: separación, columnas, altura de controles y márgenes.
- `UI/Views`: página, tabla, barra, ficha y campos.
- `UI/UserEditor.php`: valida que cada campo editable aparezca una sola vez.

La lógica existente sigue en `app/Livewire/User/UserPage.php` y `app/Modules/User/`: consulta, permisos, validación, guardado automático y eliminación. La ficha de usuario relacionada desde Instituciones reutiliza estas mismas pestañas y campos.

La presentación común está en `resources/views/components/record-editor/tabbed-editor-styles.blade.php`, `app/Livewire/Shared/EditorLayout.php` y los componentes existentes de tablas, vistas y panel lateral. Instituciones y Usuarios comparten esos estilos.

Para mover un campo cambia su asignación en Tabs.php; para cambiar el espacio modifica Layout.php. No repitas campos entre pestañas. No se agregan permisos, contraseñas ni cambios de datos con este ajuste visual.

Las vistas antiguas en resources/views/livewire/user son delegaciones de compatibilidad; modifica únicamente UI/Views para evitar diseños duplicados.
