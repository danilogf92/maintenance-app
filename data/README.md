# Maintenance APP — Prueba de lectura de datos · 0.1

## Instalación en el proyecto

El bundle completo instala este lector en la carpeta data del proyecto. Actualizar.cmd prepara configuracion.ini y correspondencias.json a partir de sus plantillas .example, únicamente cuando faltan. Las siguientes actualizaciones conservan las rutas y correspondencias que hayas modificado. El lector también prepara esos archivos al ejecutarse si todavía no existen.

## Uso

1. Aplica el bundle completo usando Actualizar.cmd con la ruta de tu proyecto.
2. Abre `configuracion.ini` y cambia únicamente la ruta:

```ini
dir="C:\Users\danil\OneDrive\Documentos\DevelopmentDanilo\ninox-app\storage\app\ninox\files"
```

La ruta debe ser la carpeta principal que contiene `1 Equipos`, `2 Bitácora Servicio`, `Otros`, etc. También se permite señalar una sola entidad o directamente su carpeta DataSanitized. Conserva las comillas. No es necesario duplicar las barras de Windows.

3. Ejecuta `LeerDatos.cmd` con doble clic o desde CMD:

```cmd
LeerDatos.cmd
```

Usa PowerShell de Windows y funciona independientemente del contenedor de Maintenance APP. Los resultados se guardan en `data/resultado/ID_EJECUCION/` dentro del proyecto. El archivo `diagnostico-ID_EJECUCION.zip` reúne la información para compartirla.

## Resultado

- `carpetas.json`: inventario de tablas y subtablas con su ruta DataSanitized, campos detectados, registros válidos/inválidos, archivos, tamaños y entidad propuesta.
- `carpetas.md`: lista legible para revisar qué carpetas se encontraron.
- `errors.json`: errores y observaciones de lectura, con carpeta/archivo y posición cuando corresponde.
- `status.json`: resultado de la ejecución y cantidades de errores/advertencias.
- `ack.json`: constancia de lo leído. Indica expresamente que no hubo importación y que falta seleccionar las entidades.

Envíame el ZIP de diagnóstico y dime qué tablas quieres importar. El ZIP contiene los inventarios y diagnósticos; los adjuntos originales permanecen en su lugar.

## Correspondencias

Hay un único archivo `correspondencias.json` que relaciona el nombre de origen con la tabla del desarrollo. Se conservan todas las equivalencias en este archivo. Los nombres se comparan ignorando mayúsculas, acentos, separadores y numeración inicial; esta normalización solo sirve para mostrar una propuesta, no modifica registros ni ejecuta importaciones.

Las tablas sin correspondencia se incluyen igualmente con `proposed_entity: null`, y una observación `mapping_missing`. Todas las tablas se generan con `import: false` para elegirlas después. Las carpetas detectadas no se agregan ni se borran del archivo de correspondencias automáticamente.

## Qué se lee

Se buscan carpetas llamadas exactamente `DataSanitized`, incluidas las que estén dentro de `Subtablas`. Se omiten DataFull, DataSanitizedBasic y los enlaces/puntos de unión. No se sustituyen por otras versiones del origen cuando falta DataSanitized.

Se usa el primer formato disponible, en este orden:

1. `DataSanitized/export/records.jsonl`
2. `DataSanitized/records.jsonl`
3. `DataSanitized/export/records.json`
4. `DataSanitized/records.json`
5. JSON individuales en `DataSanitized/records/`

Así no se cuentan dos veces las copias de registros del export y los JSON individuales. El inventario de archivos sí conserva todas sus rutas para conocer la estructura real. Los adjuntos se cuentan dentro de DataSanitized/files; archivos que estén fuera de DataSanitized no se consideran parte de esta prueba.

Los JSONL se leen línea por línea. Se detectan JSON inválidos e identificadores repetidos. Solo se guardan nombres de campos, identificadores de tabla, rutas, tamaños y cantidades; no se incluyen los valores completos de los registros ni el contenido de fotografías/PDF.

## Alcance de esta prueba

Es un inventario local. Todavía no incorpora los botones administrativos Update, Erase o Seed, ni aplica operaciones sobre la base de datos. Sus ACK no se deben interpretar como confirmaciones de importación. Cada ejecución tiene su propia carpeta y no sobrescribe los diagnósticos anteriores.

## Código y verificación

- LeerDatos.ps1: coordina lectura y resultados.
- scripts/Configuration.ps1: lee la ruta y las correspondencias sin ejecutar expresiones del archivo de configuración.
- scripts/Discovery.ps1: encuentra DataSanitized y enumera sus archivos.
- scripts/Inventory.ps1: cuenta y valida registros; genera metadatos.
- scripts/Diagnostics.ps1: escribe estado, errores y ACK.

Prueba funcional ejecutada con PowerShell 7.5.4 en Linux: origen real de Protocolos con 55 protocolos, 675 pasos y 189 adjuntos; subtablas, preferencia JSONL sin duplicados, JSON individual, origen directo DataSanitized, repetición, ZIP de diagnóstico, tabla sin correspondencia, JSON dañado, ID repetido, configuración/ruta incorrecta, origen vacío, conservación del origen y exclusión de valores originales en los resultados. Se usa sintaxis compatible con PowerShell 5.1; CMD y Windows no se ejecutaron aquí.

## Corrección 11.61.2

Cálculo de tamaños mediante acceso explícito a las claves de los archivos, compatible con Windows PowerShell 5.1. Se elimina Measure-Object sobre diccionarios (solo admitido desde PowerShell 6). Los errores de inventario de una tabla se registran en errors.json y ACK y se continúa con las siguientes. Las rutas y correspondencias configuradas se conservan.
