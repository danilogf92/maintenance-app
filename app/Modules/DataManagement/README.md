# Gestión de datos · 11.62.0

## Acceso

Admin → Configuración → Gestión de datos. Ruta `/admin/gestion-de-datos`.

El panel conserva la identidad violeta del administrador. Esta entrega implementa revisión y correspondencias; no carga ni borra datos operativos. Update, Erase y Seed figuran como pendientes, no como acciones disponibles.

## Funciones disponibles

- Resumen: tablas, registros y adjuntos detectados; tablas sin correspondencia. Los conteos proceden del diagnóstico, no se presentan como importaciones.
- Leer último diagnóstico: lee carpetas.json del resultado más reciente de data/resultado, con su errors.json si existe. La primera apertura lo carga automáticamente cuando no hay una revisión guardada y el usuario tiene permiso de gestión.
- Subir diagnóstico: acepta el ZIP de diagnóstico o carpetas.json. El original no necesita copiar sus adjuntos al panel.
- Correspondencias y campos: muestra todas las tablas sin paginación, permite buscar y elegir entidades existentes, compara campos de origen con columnas reales del destino y muestra criterios pendientes.
- Guardar correspondencias: escribe únicamente data/correspondencias.json. Conserva las entradas ajenas al inventario y evita sobrescribir cambios de otro usuario mediante una huella de versión y bloqueo de archivo.
- Relaciones implementadas: lee las claves foráneas de la base instalada y sus conteos actuales de vínculos presentes/nulos. Solo usa correspondencias guardadas. Un nulo puede ser válido en una relación opcional; no se etiqueta automáticamente como error.
- Descargar diagnóstico: genera un ZIP privado con carpetas.json, correspondencias.json, relaciones.json, errors.json, status.json y ack.json. El ACK identifica la operación como revisión, con imported=false.

## Diferencias identificadas en los datos recibidos

El diagnóstico utilizado contiene 37 tablas/subtablas, 7346 registros y 2898 adjuntos. Estos datos no se insertan en producción a partir de las pruebas ni del ZIP de actualización; el panel lee el diagnóstico que ya existe en el proyecto o el que suba el administrador.

- Clientes y centros médicos tienen destinos existentes, pero necesitan adaptadores de campos y referencias.
- Actividades/hallazgos tiene destino activities; report_id y sus fotografías necesitan transformación y verificación contra servicios.
- Equipos asociados a contrato solo aporta contrato/equipo. contract_equipment_coverages requiere además first_month/end_month; no se inventan períodos.
- Visita contiene asignaciones históricas FSE. No se convierte en maintenance_visits del calendario por coincidencia del nombre.
- Clientes–Contactos puede corresponder a contact_links, pero primero hay que recibir y verificar los contactos.
- Recomendaciones llega con value_1/value_2/value_3. El desarrollo guarda services.recommendations; todavía hay que interpretar y combinar esa fuente.
- Firmas históricas y componentes requieren una entidad equivalente antes de importarse. No se reutilizan firmas anteriores para nuevas firmas.
- Los ingenieros del historial y los usuarios con acceso siguen separados.

Las propuestas en SourceHints se muestran para revisión y nunca se guardan automáticamente. Tampoco se ejecutan SQL ni comandos a partir del nombre de una tabla recibido.

## Almacenamiento y permisos

- Configuración única: data/correspondencias.json. El lector de Windows usa el mismo archivo y los nombres se comparan con la misma normalización básica de acentos, separadores y numeración inicial.
- Diagnóstico activo: disco local privado, data-management/current/inventory.json.
- Diagnósticos descargables: disco local privado, data-management/runs/ID/.
- config/data-management.php: rutas internas de inventario y correspondencias, separadas del origen externo definido en data/configuracion.ini.
- data_management.view: acceso al apartado y descarga de diagnóstico.
- data_management.manage: cargar inventarios y guardar correspondencias.

Ambos permisos se añaden a Admin y Administrador sin eliminar permisos existentes. SuperAdmin conserva su acceso global. El permiso también exige acceso al panel y usuario activo. No se conceden permisos de borrado ni de importación en esta etapa.

Los ZIP se leen sin extraer rutas del archivo. Se limita el tamaño descomprimido de carpetas.json, se valida su estructura y se descartan valores y listas de archivos ajenos al inventario resumido. Las vistas escapan los datos recibidos. Usuarios, contraseñas, permisos y configuración de acceso no son destinos seleccionables.

## Dónde modificar

- Configuration/Entities.php: entidades permitidas y etiquetas.
- Configuration/SourceHints.php: diferencias detectadas en las tablas recibidas y sugerencias explícitamente pendientes.
- Queries/SchemaReview.php: lectura de columnas, claves foráneas y conteos de vínculos en BD.
- Queries/Reconciliation.php: comparación del inventario con los destinos elegidos.
- Actions/ReadInventory.php: validación del JSON/ZIP y minimización de los datos guardados.
- Support/InventoryStore.php: diagnóstico activo y búsqueda del último resultado del lector.
- Support/MappingStore.php: lectura/escritura segura del archivo único de correspondencias.
- Actions/ReviewDiagnostics.php: ZIP de diagnóstico y constancia de revisión.
- UI/DataManagementPage.php: controles de Filament y autorización de cada acción.
- UI/Views/: resumen, tabla de correspondencias, detalle de campos, relaciones y diagnóstico.
- public/css/pages/data-management.css: estilos limitados al nuevo apartado; conserva la paleta del administrador.

El metadato del esquema se almacena temporalmente en caché; los conteos de registros y vínculos se consultan en la base actual. Las tablas se muestran completas y las sugerencias no modifican relaciones existentes.
