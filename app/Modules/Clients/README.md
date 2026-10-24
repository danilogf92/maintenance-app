# Clientes: interfaz normal de usuarios

La ruta /operations/clients usa Livewire ClientPage y el ciclo común Workflow/UI/TabbedRecordPage con el mismo formato de Instituciones y Usuarios. No requiere acceso a /admin. Los otros módulos conservan sus pantallas actuales.

## Dónde modificar

- Configuration/Fields.php: definitions/names para la ficha normal. make conserva la definición Filament anterior.
- Configuration/Tabs.php: General, Contacto y Equipos relacionados. Cada campo se asigna una sola vez.
- Configuration/Layout.php: columnas, separación y altura de controles.
- Configuration/Columns.php y TableColumns.php: columnas, vistas iniciales y acciones de columna.
- UI/Views: tabla, barra, ficha, campos y tabla de equipos.
- Actions/SaveClient.php: reutiliza SaveRecord y las reglas/validaciones existentes.
- Queries/ClientQuery.php: búsqueda y caché por entrada/usuario; ClientCacheObserver invalida al guardar clientes o equipos.

## Comportamiento

La fila abre un panel lateral redimensionable. Guardado automático con indicador: nombre obligatorio, código generado automáticamente. El cliente se desactiva para conservar historial; no se agrega eliminación.

Las vistas Todos/Activos/Inactivos son personales, admiten favorito, columnas configurables, orden, filtros y agrupación por estado/ciudad. La edición concurrente utiliza lock_version: si alguien guardó antes, pulsa Descartar cambios y cerrar, vuelve a abrir el registro y revisa los datos; no se sobreescriben cambios silenciosamente.

Equipos relacionados se carga al abrir la pestaña. Muestra exclusivamente equipos del cliente seleccionado, con búsqueda y paginación. Su nombre abre /operations/equipment?record=ID en la pantalla existente del equipo. Equipos utiliza ahora el mismo formato tabulado.

Los permisos son los existentes: workflow.access + clients.view; clients.manage para guardar/crear. La pestaña y el contador de equipos requieren equipment.view. Técnico/Engineer y CEO consultan Clientes; los administradores pueden gestionarlos. Los controles deshabilitados no sustituyen las comprobaciones del servidor.

No se modifica la base de datos para este cambio de interfaz. Las relaciones existentes se conservan. Las colas PDF siguen funcionando igual.

Clientes comparte ahora el ciclo de edición con Workflow/UI/TabbedRecordPage.php y la ficha con resources/views/components/record-editor/tabbed-detail.blade.php. Los campos y las relaciones siguen definidos dentro de su módulo.
