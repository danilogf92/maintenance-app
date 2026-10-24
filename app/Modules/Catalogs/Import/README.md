# Catálogos Ninox: Marcas y Modelos — v11.52.1

## Alcance real

Se incluyen 41 marcas de tabla N y 53 modelos de tabla O, con 30 logos de marcas y 29 imágenes de modelos. No se mezclan marcas parecidas (por ejemplo CAE y ELEVATE) ni se corrigen nombres de origen automáticamente. Los nombres actuales se recortan en los extremos; el valor original íntegro se conserva cifrado.

La exportación de modelos entregada contiene records/*.json y files; no contiene import-plan.json. Se consolidaron los 53 registros sin cambiar valores en database/imports/ninox-catalogs/models/records.jsonl. La exportación de Marcas sí incluye su import-plan.json. El bundle conserva todos los datos recibidos, no necesita reabrir los RAR en Windows.

## Importación automática

El mismo Actualizar.cmd RUTA_DEL_PROYECTO aplica la migración y llama a `ninox:import-catalogs`, además de la importación de bitácoras anterior. No crea respaldos ni elimina los catálogos o datos existentes. No crea fabricantes, proveedores, equipos ni cuentas ficticias.

Los registros se crean en las tablas operativas `brands` y `equipment_models`. `ninox_catalog_imports` guarda tabla/ID originales, destino, hash del origen y payload cifrado con APP_KEY. `ninox_catalog_assets` registra imágenes y su integridad. Un ID numérico de Ninox nunca se usa como ID de Laravel.

Cada catálogo importado tiene un código NX-N-ID o NX-O-ID. La identidad única tabla/ID evita duplicados. Repetir la actualización conserva cambios hechos por el usuario en nombre, estado, notas, marca y campos adicionales; revalida las imágenes sin sobrescribir cambios actuales. Si cambia el origen o existe otro registro con el mismo nombre normalizado/código, se informa conflicto y no se fusionan automáticamente. La importación no realiza coincidencias aproximadas.

## Relación marca–modelo

El modelo exportado no contiene una marca explícita. Se cruzan `brands.equipment_id` y `models.equipments` usando exclusivamente IDs Ninox. Solo se asigna una marca cuando **todos** los equipos del modelo aparecen en una única marca y todos apuntan a la misma. Si hay equipos sin marca, marcas contradictorias o ningún equipo, no se asigna.

Resultado de estos archivos: **48 vínculos deducidos por equipos comunes y 5 modelos sin marca**. En Origen Ninox se identifica el vínculo inicial y su base; debe confirmarse con la exportación de Equipos. No se presenta como relación explícita del modelo original.

Sin marca quedan XVAC, Ninox Software, VENTILADOR DE ANESTESIA, UPROBE-C3 y UPROBE-C4PL. Pueden completarse manualmente cuando se verifique la marca. El valor genérico `value_10` (1 en los 53 modelos) no se interpreta como marca.

Los 209 IDs de equipos relacionados con las marcas se conservan como referencias; todavía no son equipos importados. Las 28 referencias a proveedor de marcas quedan pendientes de la tabla Proveedores. Casa comercial no equivale automáticamente a fabricante.

## Campos y consulta

Menú **Catálogos de equipos → Marcas / Modelos**:

- Marcas: General; Datos comerciales (casa comercial y procedencia); Origen Ninox (ID, proveedor, equipos originales y logo); Relaciones operativas.
- Modelos: General (incluye marca actual); Datos del modelo (descripción y visitas recomendadas por año); Origen Ninox (marca deducida, tiempos originales, documentación, equipos e imágenes); Relaciones operativas.

Las fichas importadas son editables por los permisos actuales del catálogo. La copia de origen no cambia cuando se edita la ficha. Los tiempos de preventivo, overhaul y correctivo se muestran como valores originales: **no conocemos la unidad**, por eso no se convierten a horas/días/meses. Las visitas recomendadas por año tampoco crean ni modifican planes automáticamente. La planificación sigue siendo por mes/año y frecuencia en meses.

Los 59 archivos son imágenes originales PNG/JPEG/GIF verificadas por SHA256 y formato. Se almacenan en disco local privado, nunca en public/storage. Las rutas requieren sesión activa, permiso del catálogo y correspondencia entre catálogo/registro/imagen; no se pueden intercambiar IDs entre marcas y modelos. El logo de marca no se convierte en logo de un fabricante ni se cambia el encabezado PDF existente.

## Código y pruebas

- `CatalogSource`: validación de ambos JSONL, identidades y rutas.
- `BrandEvidence`: cruce completo y conservador de equipos de origen.
- `ImportCatalogRecord`: transacción por registro, identidad y no sobrescritura.
- `CatalogImages`: SHA256, tipos permitidos, tamaño máximo 20 MiB por archivo, almacenamiento privado.
- `ImportNinoxCatalogs`: bloqueo de ejecución, orden marcas→modelos, resumen y simulación.
- `UI/Views/ninox.blade.php`: datos originales e imágenes.

```bash
php artisan ninox:import-catalogs --dry-run
php artisan ninox:import-catalogs
```

Estos comandos son diagnósticos opcionales; el actualizador ya realiza la importación. Devuelve error ante conflictos o imágenes inválidas, admite archivos faltantes como pendientes. Usa PHP/Laravel; no requiere Python para importar. Cada registro usa transacción y los originales cifrados se conservan; no hay reversión automática de datos.
