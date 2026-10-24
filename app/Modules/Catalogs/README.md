# Fabricantes, marcas y modelos — v11.48.0

## Uso práctico

En el menú normal abre Catálogos de equipos. Admin sigue en /admin y el trabajo operativo en /operations.

1. Fabricantes: registra nombre, país y sitio web. La pestaña Logo permite subir PNG/JPEG/WebP de hasta 1 MiB. Relaciona las marcas en Relaciones → Añadir marca.
2. Marcas: registra nombre y selecciona fabricante cuando la relación esté verificada. Desde Relaciones puedes iniciar un modelo con la marca preseleccionada.
3. Modelos: nombre y marca. Los modelos del mismo nombre en diferentes marcas son registros distintos.
4. Equipos: selecciona marca y luego modelo. El selector de modelos se filtra por marca. Cambiar la marca vacía el modelo para evitar una combinación incorrecta.
5. Informes: Revisión → Actualizar datos captura nombre del fabricante y logo; luego PDF → Generar PDF. El formato debe usar origen «Fabricante» para ese logo. Si no hay imagen, se utiliza el nombre del fabricante, o de la marca si falta fabricante.

Las fichas reutilizan las pestañas, panel redimensionable, guardado automático y vistas personales de Instituciones. Activos/Inactivos permiten organizar el catálogo. Relacionados muestra hasta 50 registros, con enlaces al registro y al catálogo correspondiente.

## Qué se migra y conserva

La migración 2026_10_17_000002 crea manufacturers, brands y equipment_models; añade brand_id y equipment_model_id a equipment. Agrupa los textos existentes ignorando mayúsculas y espacios repetidos, sin borrar sus valores originales. Un modelo queda asociado a su marca conocida. Si falta marca, el modelo queda pendiente. No se deducen fabricantes ni se inventan logos.

Los campos de texto equipment.brand y equipment.model siguen por compatibilidad y se sincronizan cuando se cambia el nombre del catálogo. Esa sincronización incrementa lock_version para rechazar una edición de equipo que estaba abierta con datos anteriores. Las revisiones y tareas PDF previas conservan los datos e imágenes capturados; no se regeneran automáticamente.

Al actualizar un registro en uso se protege el fabricante/marca padre cuando ya estaba asignado. Se permite completar una relación que estaba vacía. Para una identidad distinta crea otro registro. Inactivar conserva relaciones e informes; no se ofrece borrado físico. Los archivos de logos retirados se conservan y no se reutilizan como firmas.

La conversión usa bloques de 200 equipos y es idempotente. La migración puede continuar tras una interrupción sin recrear tablas/columnas ya creadas. No ejecuta una reversión automática ni genera respaldos.

## Dónde modificar

- app/Modules/Manufacturers/Configuration: campos, pestañas, reglas y disposición de Fabricantes.
- app/Modules/Brands/Configuration: configuración de Marcas.
- app/Modules/EquipmentModels/Configuration: configuración de Modelos.
- app/Modules/Catalogs/UI: comportamiento común, vistas y logo.
- app/Modules/Catalogs/Actions: guardado, validación de relaciones, normalización de textos y sincronización de Equipos.
- app/Modules/Catalogs/Queries: opciones, listados y relaciones.
- app/Modules/Equipment/Configuration/Fields.php: selección de marca/modelo en la ficha del equipo.
- app/Modules/TechnicalReports/Queries/ReportData.php y ReportTemplates/Rendering: datos y logo incorporados a la revisión del informe.

Los nombres de Fabricantes y Marcas son únicos en su catálogo. Los de Modelos son únicos dentro de su marca. Marca y Modelo admiten hasta 100 caracteres para respetar el esquema histórico de Equipos; Fabricante admite 160.

## Permisos y actualización

CatalogPermissionsSeeder añade view/manage por cada catálogo: Administrador/Admin administran; Técnico/Engineer/CEO consultan. Se mantienen los permisos personalizados existentes. SuperAdmin conserva acceso global y los usuarios inactivos quedan bloqueados. La selección de nuevos registros relacionados exige permiso de consulta; guardar exige manage. El logo se valida en servidor y nunca acepta una ruta escrita por el cliente.

Actualizar.cmd aplica migración, WorkflowSeeder y CatalogPermissionsSeeder, conserva .env/APP_KEY/storage, limpia cachés y reinicia servicios/colas. La generación PDF sigue en database/reports. El enlace public/storage se prepara para visualizar logos.

No hay catálogos de protocolos, contactos, documentación ni emisión final en esta entrega. Siguiente entidad sugerida: Contactos.

## Validación

EquipmentCatalogTest cubre datos anteriores, duplicados, relaciones, permisos, estados inactivos, bloqueo de concurrencia, logos y copia estable en informes. También se valida el recorrido con navegador y PDF real por cola. El actualizador Windows/Docker se debe verificar en Windows.
