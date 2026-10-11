# Velocidad y caché — v11.23

## Problemas encontrados

- La misma pantalla consulta varias veces marca/logo y comprueba el esquema cada vez. Colores repite comprobación y consulta.
- Cada acción de Livewire vuelve a cargar la tabla completa y los catálogos, incluso al abrir/cerrar un diálogo.
- Usuarios seleccionaba todas las columnas, incluidas contraseña y remember_token aunque la tabla no las muestra. Ahora la consulta usa solo campos de presentación.
- Se muestran todos los resultados: el navegador sigue recibiendo/procesando 500 filas. La caché reduce consultas; no elimina el coste de Blade, transporte ni DOM.
- El proyecto está dentro de OneDrive y Docker monta esa carpeta. El acceso a archivos/sincronización puede afectar tiempos; no se cambiaron volúmenes ni configuración del usuario.

## Estrategia implementada

| Datos | Duración máxima | Renovación |
|---|---|---|
| Tabla Usuarios/Instituciones | 30 segundos | Al entrar al módulo; al crear, modificar o borrar registros relacionados |
| Catálogo instituciones | 5 minutos | Al cambiar institución y al iniciar sesión |
| Marca/logo y colores | 5 minutos | Al editar la configuración y al iniciar sesión |
| Roles mostrados en tabla | Como tabla Usuarios | Eventos Spatie de asignar/retirar roles o permisos; cambios del rol |
| Permisos, sesión y autorización | Sin caché propia de páginas | Se comprueban en cada petición |
| Pestañas/favorita | Consulta en base de datos | Se guardan inmediatamente y se leen al entrar |

Las claves incluyen versión de datos, usuario, filtros y una versión de entrada. Cambiar de registro aprovecha la caché; volver a entrar obtiene datos frescos para ese usuario. Renovar por cada clic destruiría la utilidad de la caché, por eso las lecturas la reutilizan y las escrituras invalidan.

Los observadores invalidan al modificar y otra vez después del commit para evitar que una lectura concurrente haya repoblado datos anteriores. Esta entrega activa los eventos de Spatie para detectar cambios de relaciones. No requiere colas ni procesos auxiliares.

Los listados almacenan solo los campos de presentación; no se cachean contraseñas, tokens ni respuestas HTML privadas completas. Las claves de listados distinguen al usuario. Los permisos actuales se verifican antes de leer la instantánea, y al escribir se consulta/autorizan los modelos reales.

## Dónde cambiarlo

- `config/page-cache.php`: TTL, activar/desactivar y almacén.
- `app/Support/Cache/PageDataCache.php`: claves/versiones y renovación al entrar.
- `app/Support/Cache/InvalidatesPageData.php`: dependencias entre datos.
- `AppServiceProvider`: observadores y eventos registrados.
- `UserQuery`/`InstitutionQuery`: datos seleccionados, filtros y carga.

Por defecto utiliza un almacén `page-data` separado en `storage/framework/cache/page-data`, configurado en `config/cache.php`. Conserva el almacén de sesiones y permisos actual. En los tests funcionales se usa array y el benchmark usa archivos temporales aislados; Redis puede configurarse mediante `PAGE_CACHE_STORE=redis` cuando exista un servicio Redis compartido. No se incorpora Redis ni se cambia .env en esta actualización.

Opciones de .env, solo si quieres modificarlas tú:

```dotenv
PAGE_CACHE_ENABLED=true
PAGE_CACHE_STORE=page-data
```

Para limpiar esta caché manualmente:

```cmd
docker compose exec app php artisan maintenance:cache-clear
```

Este comando limpia el almacén dedicado; con otro almacén solo rota las versiones de páginas. No cierra sesiones ni borra permisos. En el driver file, las entradas vencen lógicamente; esta limpieza también libera archivos de versiones antiguas. Redis gestiona su expiración física automáticamente.

## Límites de actualización

Los eventos se emiten en operaciones Eloquent del sistema. SQL externo, Query Builder update/delete e importaciones masivas que omiten eventos deben invalidar explícitamente `PageDataCache`; si no lo hacen, las tablas se renuevan al reingresar o al vencer sus 30 segundos, y los catálogos/configuración a los 5 minutos. Añade invalidación al finalizar futuros importadores Ninox.

No se usa polling, no se recalientan 500 registros en cada login y no se cachean decisiones de acceso. Si hay varios servidores, usar un almacén compartido para que las versiones e invalidaciones lleguen a todos.

## Medición

La suite tiene `PagePerformanceTest`: crea 500 usuarios y 500 instituciones exclusivamente en SQLite en memoria, carga una instantánea fría y otra caliente y registra consultas/tiempos. El escenario incluye tablas, catálogo, marca y paleta. Los resultados se anotan en `validation-v11.23.md`. Estos tiempos no son los del servidor Windows/Docker ni miden el render completo de la página.

Para una siguiente mejora con grandes volúmenes, medir primero red, render Blade y DOM; después introducir paginación/virtualización junto con navegación primero/último basada en la consulta completa. Así se conserva el comportamiento acordado de las flechas.
