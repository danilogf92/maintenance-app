# Interfaz, datos y revisión — v11.37

## Uso

Pulsa el nombre o la flecha de una columna para abrir sus opciones: orden,
filtro y, en categorías habilitadas, agrupación. Los filtros de texto buscan
coincidencias parciales; los números, un valor exacto; las fechas, el día;
las categorías permiten buscar una opción y seleccionarla. Aplicar ejecuta
una petición; escribir o cancelar no modifica la tabla.

La agrupación inicia cerrada. Una cabecera muestra la categoría y cantidad de
registros, sin repetir el nombre de la columna delante de cada categoría.
Puedes expandir una, todas o colapsarlas. Navegar al primero/último/anterior/
siguiente abre el grupo del registro antes de desplazar la tabla.

El botón con una tabla pequeña abre Configurar vista. Una tabla permite
mostrar/ocultar y mover columnas. Pulsar su nombre abre las mismas opciones
de filtro, orden y agrupación; así están disponibles también en móvil.
La favorita sigue siendo única por usuario y módulo y se guarda en DB.
Se retiraron el botón Filtros, sus paneles y la selección antigua de institución.
Los filtros de pestaña Todos/Empresas/etc. se mantienen.

## Dónde modificar columnas

Instituciones:

- app/Modules/Institutions/Configuration/Columns.php: nombres, etiquetas y pestañas.
- app/Modules/Institutions/Configuration/TableColumns.php: origen, tipo y acciones.
- app/Modules/Institutions/UI/Views/table.blade.php: presentación de los valores.
- app/Modules/Institutions/Configuration/Fields.php: campos del editor.

Usuarios: app/Modules/User/UserDefinition.php y UserTableColumns.php;
presentación en resources/views/livewire/user/table.blade.php.
La base de datos necesita una migración si el campo aún no existe.
La carpeta Institutions contiene el README práctico y sus pruebas específicas.

## Reinicio solicitado de datos locales

Esta versión ejecuta una sola vez `maintenance:demo-refresh` al instalarse.
Elimina los usuarios que no tengan rol SuperAdmin y las instituciones que no
pertenezcan a los SuperAdmin. Mantiene todas las cuentas SuperAdmin, sus
contraseñas y sus instituciones, además de marca, colores, roles y permisos.
También elimina sesiones de los usuarios retirados, tokens de recuperación y
el historial técnico de creación de registros. Las vistas de SuperAdmin permanecen.

Crea 500 instituciones y 500 usuarios adicionales de prueba: nueve ciudades,
cinco tipos, seis cargos, distintos estados y relaciones agrupadas de usuarios.
Los datos son sintéticos, los correos usan example.test y las contraseñas son
aleatorias sin publicarse. Ningún usuario de prueba obtiene acceso administrativo.

El reinicio está limitado a APP_ENV=local; testing se permite solo para verificarlo.
Si se ejecuta en producción se detiene sin borrar. Es transaccional: un fallo
durante la sustitución revierte el cambio. El marcador demo-data-v11.37.txt evita
que repetir Actualizar.cmd vuelva a borrar datos agregados después de instalar.
No se generan respaldos, según la preferencia del proyecto.

## Caché y reinicio en cada actualización

El instalador ejecuta optimize:clear, maintenance:cache-clear y
permission:cache-reset, y reinicia el servicio app de Docker al finalizar.
Estos pasos se mantienen en las próximas versiones del instalador.
No cambia APP_KEY ni elimina las sesiones del SuperAdmin. Recarga con Ctrl+F5.

## Revisión de diseño y seguridad realizada

- Falta de registro del componente Institutions corregida desde v11.36.
- Menús y configuración usan dialog nativo: foco dentro del modal, Escape y retorno.
- Filtro con borrador para reducir actualizaciones mientras se escribe.
- Grupos se abren sin peticiones y navegación respeta las filas ocultas.
- IDs/estados de registro, filtros y columnas visibles protegidos con Locked.
- Mover columnas acepta únicamente los desplazamientos -1 y 1.
- Acciones de columna limitadas por configuración; vistas limitadas al propietario.
- Validaciones y permisos se comprueban en las acciones de PHP.
- Relaciones siguen limitadas a la institución y requieren permiso de Usuarios.
- Se retiró una referencia de Filament a CSS inexistente y activos antiguos sin uso.

La revisión cubre estos módulos y cambios; no equivale a una auditoría completa
de infraestructura, dependencias o despliegue. La validación visual final debe
hacerse en tu navegador; aquí se ejecutan pruebas PHP, HTTP y de controladores JS.
