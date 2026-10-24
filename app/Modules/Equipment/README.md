# Equipos — interfaz normal de usuarios

Ruta /operations/equipment. Equipos adopta el mismo formato visual que Instituciones, Usuarios y Clientes. El panel /admin sigue separado.

## Configuración

- Fields.php: definitions/names para la ficha normal; make conserva la definición Filament anterior.
- Tabs.php: General (nombre, marca, modelo, serie y estado), Ubicación (cliente, institución y área), Tickets.
- Layout.php: columnas, márgenes, separación y altura de controles.
- Columns.php y TableColumns.php: columnas, vistas por estado, agrupaciones y traducciones de estados.
- UI/Views: tabla, barra, campos y tabla de tickets. La ficha comparte components/record-editor/tabbed-detail.blade.php con Clientes.
- EquipmentPage usa Workflow/UI/TabbedRecordPage para selección, permisos, guardado automático, reintentos y edición concurrente.
- SaveEquipment reutiliza SaveRecord y Rules.php. EquipmentQuery y EquipmentOptions separan consultas y búsquedas.

## Uso

Pulsa + y completa General y Ubicación. Nombre, estado, cliente e institución son obligatorios; el código se genera al guardar. Hasta completar esos datos la ficha sigue siendo un borrador que puede cerrarse sin insertar registros. No hay botón Guardar: la cabecera muestra el resultado del guardado automático.

La serie NO es única. Dos equipos pueden compartirla. Marca y modelo siguen siendo texto libre; sus catálogos están pendientes.

Cliente e institución tienen búsqueda. Los selectores mantienen el valor seleccionado aunque no aparezca entre los primeros 30 resultados. Un cliente inactivo no puede usarse para crear un equipo. Si el equipo tiene tickets, sus relaciones de propietario/institución quedan bloqueadas tanto en interfaz como en servidor; todavía no existe el proceso de transferencia histórica. El área puede editarse.

Tickets se carga al abrir su pestaña, con búsqueda y paginación. Solo muestra tickets del equipo que el usuario puede consultar. El contador Tickets visibles sigue ese mismo alcance. El código abre la pantalla actual del ticket; Tickets todavía no se ha rediseñado.

Las vistas personales permiten favoritos, columnas, filtros, orden y agrupación por estado, marca, modelo, cliente o institución. Los estados muestran etiquetas en español. Los cambios de equipos, clientes, instituciones y tickets invalidan la caché de Equipos.

Los permisos existentes se conservan: equipment.view para consultar, equipment.manage para crear/editar, clients.view para elegir/ver clientes, tickets.view para la pestaña/contador. Técnico/Engineer consulta equipos y solo ve tickets asignados; CEO consulta; administradores gestionan según permisos. Los controles deshabilitados no sustituyen las validaciones del servidor.

Si otra persona guardó antes, se rechaza el cambio y aparece Descartar cambios y cerrar para recargar el registro. No se elimina historial; usa Estado.

No se agregan tablas ni migraciones para este ajuste. Componentes, repuestos, documentos técnicos, garantías, transferencias y nuevos campos de Ninox siguen pendientes.
