# Filtros, orden y agrupación por columna — v11.34

## Uso

En Usuarios o Instituciones, pulsa la cabecera de una columna. Su menú permite:

- Orden ascendente o descendente. Un indicador ↑/↓ señala la columna ordenada.
- Filtro de esa columna: las categorías ofrecen una lista; el texto busca por
  contenido sin distinguir mayúsculas; los números requieren el valor exacto;
  las fechas admiten texto en formato AAAA-MM-DD.
- Agrupar por una categoría, con cabecera y cantidad de registros en cada grupo.

Puedes combinar filtros de varias columnas; también se aplican la búsqueda y
los filtros existentes de la pestaña. El menú ofrece Quitar filtro y la barra
superior permite quitar cada filtro, desagrupar o Restablecer tabla.
Solo hay una columna de orden y una de agrupación a la vez. Ordenar la columna
agrupada en descendente invierte también el orden de las categorías; ordenar
otra columna organiza los registros dentro de cada grupo.

Las opciones se guardan en DB por usuario, módulo y pestaña, en
`operation_views.table_settings`. Al entrar en la pestaña favorita también se
recuperan sus opciones. Cada pestaña mantiene una configuración independiente.
Los encabezados con estos menús están disponibles en la tabla de escritorio;
el diseño móvil actual oculta las cabeceras. La barra de opciones activas permite
quitar filtros y agrupaciones en móvil.

## Elegir columnas desde PHP

Edita `app/Modules/User/UserTableColumns.php` para Usuarios y
`app/Modules/Institution/InstitutionTableColumns.php` para Instituciones.
Cada columna tiene `filterable`, `sortable`, `groupable`, `field` y `kind`.
Por ejemplo, antes del `return $columns`:

```php
$columns['email']['filterable'] = false;
$columns['job_title']['groupable'] = true;
$columns['phone']['sortable'] = false;
```

`kind` admite text, category, number y date. `field` es un atributo ya cargado
(por ejemplo institution.name o users_count). El navegador nunca envía nombres
SQL. Una acción deshabilitada en PHP se rechaza también en el servidor; las
opciones guardadas que ya no estén permitidas se ignoran al cargar la vista.

Usuarios permite agrupar por institución, cargo, roles, estado, acceso admin y
tema. Instituciones permite agrupar por tipo y ciudad. Para usuarios con varios
roles, la combinación completa forma una categoría: no se duplican sus filas.
Los valores vacíos se muestran como Sin dato.

## Código y rendimiento

- `Modules/Shared/RecordTable.php`: filtra, ordena y agrupa la colección cargada.
- `Livewire/Shared/HasTableTools.php`: valida acciones y guarda preferencias.
- `components/record-editor/column-header.blade.php`: menú de cada cabecera,
  fuera del contenedor de scroll para evitar recortes.
- `table-tools-status` y `table-tools-styles`: indicadores y presentación común.

Usa los snapshots de caché actuales con relaciones precargadas; la transformación
no hace consultas adicionales ni guarda las preferencias en caché. Los cambios
de registros siguen invalidando los snapshots mediante los observadores actuales.
No se agrupa ni limita en SQL, por lo que no desaparecen registros del navegador
primero/anterior/siguiente/último: esas acciones leen el orden visible de las filas.

El instalador agrega únicamente la columna JSON mediante su migración específica,
además de las dos migraciones anteriores si faltan. No crea respaldos ni ejecuta
seeders. Pruebas: persistencia por pestaña, relación/estado, orden numérico,
filtros combinados, grupos y cantidades, acciones no permitidas, aislamiento de
vistas y ausencia de consultas en la transformación. No hubo prueba visual en navegador.
