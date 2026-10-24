# Navegación de registros — v11.48.1

Primero, Anterior, Siguiente y Último conservan la pestaña del editor al cambiar de registro. Si estabas en Equipos → Tickets, el equipo siguiente muestra sus propios tickets. Ocurre lo mismo con Servicios → Informes, Instituciones → Usuarios y las pestañas de los demás módulos.

La navegación guarda y valida el registro actual antes de abrir el destino. Si hay un error, permanece en el registro actual y no descarta los cambios. Los botones se mantienen ocupados hasta cargar las relaciones del destino para evitar solicitudes solapadas desde esas flechas. Se conservan los permisos y el orden de la tabla.

Crear un registro, cerrar y volver a abrir la ficha o seleccionarla desde la tabla conserva la apertura habitual. No se guardan pestañas entre módulos o sesiones del navegador.

## Código

resources/views/components/record-editor/record-navigation.blade.php contiene las flechas compartidas. Antes de editar recuerda el ID de la pestaña seleccionada. Tras abrir el destino y finalizar la actualización visual, restaura esa pestaña y espera su relación si existe.

Las pestañas de tabbed-detail.blade.php, Institutions/UI/Views/editor.blade.php y Users/UI/Views/tabs.blade.php ofrecen el evento interno record-navigation-select y data-record-relation. El evento restaura solo la selección visual; la navegación solicita la relación al servidor una sola vez. Los componentes relacionados conservan claves distintas por registro, para mostrar datos del destino y no del anterior.

No requiere migraciones ni dependencias nuevas. Se incluye en el paquete completo con el mismo Actualizar.cmd, sin respaldos automáticos.
