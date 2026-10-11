# Institutions — primera prueba v11.35

## Qué probar

1. Abre Instituciones y selecciona un registro.
2. Cambia entre General y Contacto; los valores permanecen y se guardan automáticamente.
3. Abre Usuarios: se consulta únicamente la institución seleccionada, con búsqueda y paginación.
4. Pulsa el nombre de un usuario: se abre su formulario reutilizado. Cambia el cargo,
   espera el guardado y cierra con la X. Vuelves a la institución sin cerrar su editor.
5. En móvil, desliza horizontalmente las pestañas; los campos se distribuyen en una columna.
6. Al seleccionar otra institución, el editor vuelve a la primera pestaña y las relaciones
   se cargarán cuando las abras. El navegador de registros principal sigue disponible.

Esta prueba abre y edita usuarios existentes. No implementa aún crear/vincular/desvincular
usuarios desde la relación, Equipos, Contratos ni el diseñador de arrastrar y soltar.
No muestra tablas de esos módulos como si ya existieran.

## Empieza por InstitutionModule.php

`InstitutionModule.php` registra las vistas y componentes, reúne la configuración y
comprueba que no haya campos desconocidos, duplicados o sin pestaña.
La configuración del editor es común para todos los usuarios. Las vistas favoritas,
columnas y filtros de tabla siguen siendo independientes por usuario y pestaña.

## Configuración

| Archivo | Qué cambiar |
| --- | --- |
| Configuration/Fields.php | Etiqueta, tipo, requerido, longitud máxima y opciones de selectores |
| Configuration/Tabs.php | Pestañas, títulos de secciones, orden, ubicación y ancho de campos |
| Configuration/Layout.php | Espaciado, márgenes, columnas y altura de controles |
| Configuration/Rules.php | Validaciones del servidor y unicidad del código |
| Configuration/Permissions.php | Permisos del módulo y de usuarios relacionados |
| Configuration/Relations.php | Columnas, acciones disponibles y tamaño de página de la relación |
| Configuration/Columns.php | Etiquetas de tabla y pestañas de listado predeterminadas |
| Configuration/TableColumns.php | Qué columnas pueden filtrarse, ordenarse o agruparse |

### Mover un campo

En Tabs.php, quita `contact_email` de la lista de Contacto y agrégalo a General.
No copies su definición. Su validación y guardado permanecen en Fields.php/Rules.php.
Cada campo debe aparecer exactamente una vez. Puedes renombrar pestañas y crear secciones.

### Ancho de campos y secciones

En una sección de Tabs.php puedes agregar:

```php
'columns' => 2,
'widths' => ['name' => 'full'],
```

`full` ocupa toda la fila; sin excepción, cada campo ocupa una columna.
El panel estrecho usa `mobile_columns` incluso en PC; el panel ancho respeta `columns`.

### Separación

En Layout.php elige `spacing`: compact (10 px), normal (16 px) o wide (24 px).
`label_gap` separa label y control; `section_gap` separa secciones; `padding` define
el margen interior; `control_height` establece la altura mínima del control.
Los tonos oscuro y claro y los indicadores de validación son compartidos.

### Reglas

Campos obligatorios, longitudes y opciones vienen de Fields.php y se comprueban en
Rules.php en el servidor. El código único se mantiene allí. Una condición visual
no concede permiso para modificar datos. En esta prueba no hay un lenguaje de
condiciones ni ejecución de reglas escritas por usuarios.

## Responsabilidades

- UI/InstitutionPage.php: ciclo del editor y listado; reutiliza RecordEditor compartido.
- UI/Views/editor.blade.php: pestañas y composición declarada en Tabs.php.
- UI/Views/field.blade.php: dibuja los controles de Fields.php.
- Actions/SaveInstitution.php y DeleteInstitution.php: operaciones autorizadas.
- Queries/InstitutionQuery.php: snapshot de tabla y opciones de instituciones.
- Queries/InstitutionUsersQuery.php: consulta acotada y autorizada de la relación.
- UI/RelatedUsers.php: búsqueda, paginación y apertura de un usuario vinculado.
- UI/RelatedUserEditor.php: adapta UserPage y reutiliza sus campos, servicios,
  permisos, validaciones y guardado; no duplica esas reglas.
- Tests/: pruebas funcionales y comprobación automática de límites de código.

Los campos permanecen montados al cambiar pestaña. La relación se monta al abrirla
por primera vez, usa su propia paginación y se reinicia cuando cambia la institución.
Al abrir una relación se intenta guardar cualquier cambio pendiente del padre;
un borrador incompleto no crea filas ni carga relaciones.

## Conexiones externas visibles

`AppServiceProvider` llama a InstitutionModule::register(). La ruta de instituciones
apunta a UI/InstitutionPage.php. El modelo App/Models/Institution y las migraciones
existentes conservan su ubicación para mantener relaciones, caché y datos actuales.

Los archivos anteriores en Livewire/Institution y Modules/Institution son puentes
explícitos de compatibilidad. Filament delega mediante InstitutionManager hacia las
acciones nuevas. No hay una segunda validación de instituciones en esos puentes.
La documentación de configuración anterior debe leerse ahora con las rutas nuevas.

RecordEditor, record-form y componentes del editor siguen en sus carpetas compartidas;
son el soporte reutilizable. Todo comportamiento específico de Institutions está aquí.

## Estándar y verificaciones

Código propio del módulo: máximo 300 líneas por archivo y 50 por función; funciones
cortas, nombres descriptivos y responsabilidades separadas. CodeLimits comprueba
funciones PHP mediante tokens. La prueba verifica estos límites automáticamente.
Los módulos anteriores se irán adaptando al estándar al reorganizarlos.

Ejecutar desde el proyecto:

```cmd
docker compose exec app php vendor/bin/phpunit --filter=InstitutionsModuleTest
```

PHPUnit descubre tests/Feature/InstitutionsModuleTest.php, que carga las pruebas de
esta carpeta. Se comprueban configuración, carga diferida, guardado, permisos,
aislamiento de usuarios relacionados y preservación de contraseñas.
La prueba JS tests/Frontend/institution-tabs.cjs comprueba el controlador de pestañas.
No se realizó prueba visual en navegador.

La actualización usa Actualizar.cmd, conserva datos y no agrega migraciones nuevas
para esta prueba. No genera respaldos ni ejecuta seeders. El instalador conserva su
comprobación de hashes: no sobrescribe silenciosamente código modificado localmente.
Agregar una columna nueva a la DB requiere una migración y su correspondiente modelo;
Fields.php organiza controles, no cambia el esquema por sí solo.
